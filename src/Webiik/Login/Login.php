<?php
declare(strict_types=1);

namespace Webiik\Login;

use Webiik\Login\Storage\StorageInterface;
use Webiik\Cookie\Cookie;
use Webiik\Session\Session;
use Webiik\Token\Token;

class Login
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * @var Session
     */
    private $session;

    /**
     * Login session key
     * @var string
     */
    private $sessionKey = 'logged';

    /**
     * User id from last login check using method isLogged
     * @var int|string
     */
    private $uid;

    /**
     * Logout reason constants
     */
    public const MANUAL = 1, AUTO = 2;

    /**
     * Logout reason
     * @var int
     */
    private $logoutReason;

    /**
     * Time in minutes to auto logout user on inactivity between two requests
     *
     * Note:
     * Not available for permanent login.
     * 0 - no auto logout
     *
     * @var int
     */
    private $autoLogoutTime = 0;

    /**
     * Permanent identifier Storage
     * @var StorageInterface|callable|bool
     */
    private $permanentLoginStorage = false;

    /**
     * How many days to keep permanent cookie and identifiers when user is not active
     * @var int
     */
    private $permanentLoginDuration;

    /**
     * Name of permanent login cookie
     * @var string
     */
    private $permanentCookieName = 'PC';

    /**
     * @param Token $token
     * @param Cookie $cookie
     * @param Session $session
     */
    public function __construct(Token $token, Cookie $cookie, Session $session)
    {
        $this->token = $token;
        $this->cookie = $cookie;
        $this->session = $session;
    }

    /**
     * @param callable $factory
     * @param int $days
     */
    public function setPermanentLoginStorage(callable $factory, int $days = 30): void
    {
        $this->permanentLoginStorage = $factory;
        $this->permanentLoginDuration = $days;
    }

    /**
     * @param string $name
     */
    public function setPermanentCookieName(string $name): void
    {
        $this->permanentCookieName = $name;
    }

    /**
     * @param int $sec
     */
    public function setAutoLogoutTime(int $sec): void
    {
        $this->autoLogoutTime = $sec;
    }

    /**
     * @param string $sessionKey
     */
    public function setSessionKey(string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }

    /**
     * Resolute login by namespace (e.g. lang, app part, ...)
     *
     * Note:
     * If login namespace is set then methods login, updateTs, isLogged,
     * isAuth and logout are valid only for that namespace.
     *
     * @param string $name
     */
    public function setNamespace(string $name): void
    {
        $name = strtolower($name);
        $this->sessionKey .= '_' . $name;
        $this->permanentCookieName .= '_' . $name;
    }

    /**
     * Log in the user
     * @param string|int $uid
     * @param bool $permanent
     */
    public function login($uid, bool $permanent = false): void
    {
        $this->session->sessionRegenerateId();
        $this->session->setToSession($this->sessionKey, [
            'uid' => $uid,
            'ts' => $_SERVER['REQUEST_TIME'],
        ]);

        if ($permanent && $this->permanentLoginStorage) {
            try {
                $tokens = $this->createPermanentCookie();
                $this->getStorage()->store(
                    $uid,
                    $tokens['selector'],
                    hash('sha256', $tokens['key']),
                    (int)($_SERVER['REQUEST_TIME'] + ($this->permanentLoginDuration * 24 * 60 * 60))
                );
            } catch (\Exception $exception) {
                // Can't create permanent login due to missing tokens.
                // It's not a reason to stop the app. So just ignore it and use
                // only regular login.
            }
        }
    }

    /**
     * Check if user is logged
     * @return bool
     */
    public function isLogged(): bool
    {
        $isLogged = false;

        // Try to get login session
        if ($this->session->isInSession($this->sessionKey)) {
            $loginSession = $this->session->getFromSession($this->sessionKey);
            $this->setLoginCheckCredentials($loginSession['uid']);
            $isLogged = true;
        }

        // Try to delete expired permanent login identifiers, when permanent login storage is used
        if ($this->permanentLoginStorage) {
            $this->deleteExpiredPermanentIdentifiers();
        }

        // When it's necessary and it's available, try to get login credentials from permanent identifier
        if (!$isLogged && $this->permanentLoginStorage && $this->cookie->isCookie($this->permanentCookieName)) {
            // Try to get permanent cookie tokens
            $cookieTokens = $this->getPermanentCookieTokens();
            if (!$cookieTokens) {
                return $isLogged;
            }

            // Try to get permanent identifier data by cookie selector?
            $identifierData = $this->getStorage()->get($cookieTokens['selector']);
            if (!$identifierData) {
                return $isLogged;
            }

            // Is token from permanent identifier same as token from permanent cookie
            if (!$this->token->compare(hash('sha256', $cookieTokens['key']), $identifierData['key'])) {
                return $isLogged;
            }

            // Update permanent login cookie and identifiers time
            $this->updatePermanentCookieExpiration();
            $this->getStorage()->updateExpiration($cookieTokens['selector'], $this->permanentLoginDuration * 24 * 60 * 60);

            // Login the user to avoid repeated permanent login checks and so speed up authentication
            $this->login($identifierData['uid'], false);
            $this->setLoginCheckCredentials($identifierData['uid']);
            $isLogged = true;
        }

        // Auto logout (not available for permanent login)
        if ($isLogged && $this->autoLogoutTime && !$this->cookie->isCookie($this->permanentCookieName)) {
            if (isset($loginSession) && $loginSession['ts'] + $this->autoLogoutTime < $_SERVER['REQUEST_TIME']) {
                $this->logout();
                $this->logoutReason = self::AUTO;
                $isLogged = false;
            }
        }
        $this->updateAutoLogoutTs();

        return $isLogged;
    }

    /**
     * Log out the user
     */
    public function logout(): void
    {
        // Set logout reason
        $this->logoutReason = self::MANUAL;

        // Delete login session
        $this->session->delFromSession($this->sessionKey);

        // Delete permanent login cookie and identifier
        if ($this->permanentLoginStorage && $this->cookie->isCookie($this->permanentCookieName)) {
            $cookieTokens = $this->getPermanentCookieTokens();
            if ($cookieTokens) {
                $this->getStorage()->delete($cookieTokens['selector']);
            }
            $this->cookie->delCookie($this->permanentCookieName);
        }
    }

    /**
     * Get logout reason
     *
     * Note:
     * 1 - self::MANUAL
     * 2 - self::AUTO
     *
     * @return int
     */
    public function getLogoutReason(): int
    {
        return $this->logoutReason;
    }

    /**
     * @return int|string
     */
    public function getUserId()
    {
        return $this->uid;
    }

    /**
     * Update timestamp in login session with timestamp of current request
     *
     * Note:
     * It's necessary to update this timestamp with every single request when user
     * actively interacts with the app to make auto-logout feature working properly.
     *
     * Warning:
     * Never update timestamp before calling isLogged method user would be never
     * automatically logged out.
     */
    public function updateAutoLogoutTs(): void
    {
        if (session_status() != PHP_SESSION_NONE && $this->session->isInSession($this->sessionKey)) {
            $_SESSION[$this->sessionKey]['ts'] = $_SERVER['REQUEST_TIME'];
        }
    }

    /**
     * Get storage for permanent login identifiers
     * @return StorageInterface
     */
    private function getStorage(): StorageInterface
    {
        // Instantiate storage only once
        if (is_callable($this->permanentLoginStorage)) {
            $storage = $this->permanentLoginStorage;
            $this->permanentLoginStorage = $storage();
        }
        return $this->permanentLoginStorage;
    }

    /**
     * Delete expired permanent login identifiers, with the probability as same as to delete expired sessions
     */
    private function deleteExpiredPermanentIdentifiers(): void
    {
        $max = (int)ini_get('session.gc_divisor');
        $probability = (int)ini_get('session.gc_probability');
        if (mt_rand(1, $max) <= $probability) {
            $this->getStorage()->deleteExpired($this->permanentLoginDuration * 24 * 60 * 60);
        }
    }

    /**
     * Create permanent login cookie and return permanent login identifier tokens
     * @return array
     * @throws \Exception
     */
    private function createPermanentCookie(): array
    {
        $tokens = [
            'selector' => $this->token->generate(),
            'key' => $this->token->generate(),
        ];
        $this->cookie->setCookie(
            $this->permanentCookieName,
            $tokens['selector'] . '.' . $tokens['key'],
            (int)($_SERVER['REQUEST_TIME'] + ($this->permanentLoginDuration * 24 * 60 * 60))
        );
        return $tokens;
    }

    /**
     * Update expiration of permanent cookie
     */
    private function updatePermanentCookieExpiration(): void
    {
        $this->cookie->setCookie(
            $this->permanentCookieName,
            $this->cookie->getCookie($this->permanentCookieName),
            (int)($_SERVER['REQUEST_TIME'] + ($this->permanentLoginDuration * 24 * 60 * 60))
        );
    }

    /**
     * Get selector and key stored in permanent login cookie
     * @return array
     */
    private function getPermanentCookieTokens(): array
    {
        $cookieVal = $this->cookie->getCookie($this->permanentCookieName);

        if (strlen($cookieVal) != 65) {
            return [];
        }

        $cookieVal = explode('.', $cookieVal, 2);
        if (!isset($cookieVal[0], $cookieVal[1])) {
            return [];
        }

        return [
            'selector' => $cookieVal[0],
            'key' => $cookieVal[1],
        ];
    }

    /**
     * Set user id found during last login check
     * @param string|int $uid
     */
    private function setLoginCheckCredentials($uid): void
    {
        $this->uid = $uid;
    }
}
