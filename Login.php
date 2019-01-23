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
     * User id from last login check using methods: isLogged. isAuthorised
     * @var int|string
     */
    private $uid;

    /**
     * User role from last login check using methods: isLogged. isAuthorised
     * @var string
     */
    private $role;

    /**
     * All allowed user roles and associated actions e.g. ['user' => ['account-read']]
     * @var array
     */
    private $allowedAuthority = [];

    /**
     * Time in sec to auto logout user on inactivity between two requests
     *
     * Note:
     * Not available for permanent login.
     * 0 - no auto logout
     *
     * @var int
     */
    private $autoLogoutTime = 0;

    /**
     * Indicates if auto logout timestamp has been updated during current request
     * @var bool
     */
    private $autoLogoutTsUpdated = false;

    /**
     * Permanent identifier Storage
     * @var StorageInterface|callable|bool
     */
    private $permanentLoginStorage = false;

    /**
     * Name of permanent login cookie
     * @var string
     */
    private $permanentCookieName = 'PC';

    /**
     * Time in seconds to keep permanent cookie and identifiers alive
     *
     * Note:
     * 0 - unlimited
     *
     * @var int
     */
    private $permanentLoginTime = 30 * 24 * 60 * 60;

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
     */
    public function setPermanentLoginStorage(callable $factory): void
    {
        $this->permanentLoginStorage = $factory;
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
    public function setPermanentLoginTime(int $sec): void
    {
        $this->permanentLoginTime = $sec;
    }

    /**
     * @param string $sessionKey
     */
    public function setSessionKey(string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }

    /**
     * Resolute login by sections (e.g. lang, app part, ...)
     *
     * Note:
     * If login section is set then methods login, updateTs, isLogged,
     * isAuth and logout are valid only for that section.
     *
     * @param string $name
     */
    public function setLoginSection(string $name): void
    {
        $name = strtolower($name);
        $this->sessionKey .= '_' . $name;
        $this->permanentCookieName .= '_' . $name;
    }

    /**
     * @param int $sec
     */
    public function setAutoLogoutTime(int $sec): void
    {
        $this->autoLogoutTime = $sec;
    }

    /**
     * @param string $role
     * @param array $actions
     */
    public function setAllowedAuthority(string $role, array $actions = []): void
    {
        $this->allowedAuthority[$role] = $actions;
    }

    /**
     * Log in the user
     * @param string|int $uid
     * @param bool $permanent
     * @param string $role
     */
    public function login($uid, bool $permanent = false, string $role = ''): void
    {
        $this->session->sessionRegenerateId();
        $this->session->setToSession($this->sessionKey, [
            'uid' => $uid,
            'role' => $role,
            'ts' => $_SERVER['REQUEST_TIME'],
        ]);

        if ($permanent) {
            try {
                $tokens = $this->createPermanentCookie();
                $this->getStorage()->store(
                    $uid,
                    $role,
                    $tokens['selector'],
                    hash('sha256', $tokens['key']),
                    $this->permanentLoginTime ? (int)($_SERVER['REQUEST_TIME'] + $this->permanentLoginTime) : $this->permanentLoginTime
                );
            } catch (\Exception $exception) {
                // Permanent login can't be created due to missing tokens.
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
            $this->setLoginCheckCredentials($loginSession['uid'], $loginSession['role']);
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

            // Login the user to avoid repeated permanent login checks and so speed up authentication
            $this->login($identifierData['uid'], false, $identifierData['role']);
            $this->setLoginCheckCredentials($identifierData['uid'], $identifierData['role']);
            $isLogged = true;
        }

        // Auto logout (not available for permanent login)
        if ($isLogged && $this->autoLogoutTime && !$this->cookie->isCookie($this->permanentCookieName)) {
            if (isset($loginSession) && $loginSession['ts'] + $this->autoLogoutTime < $_SERVER['REQUEST_TIME']) {
                $this->logout();
                $isLogged = false;
            }
        }
        $this->updateAutoLogoutTs();

        return $isLogged;
    }

    /**
     * Check if user is authorised
     * @param string $role
     * @param array $actions
     * @return bool
     */
    public function isAuthorized(string $role, array $actions = []): bool
    {
        if (!$this->isLogged()) {
            return false;
        }

        $loginSession = $this->session->getFromSession($this->sessionKey);

        // Check if user have required role
        if ($loginSession['role'] != $role) {
            return false;
        }

        // Check if role is available (allowed)
        if (!isset($this->allowedAuthority[$loginSession['role']])) {
            return false;
        }

        // Check if user can do all required actions
        foreach ($actions as $action) {
            if (!in_array($action, $this->allowedAuthority[$loginSession['role']])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Log out the user
     */
    public function logout(): void
    {
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
     * @return int|string
     */
    public function getUserId()
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getUserRole(): string
    {
        return $this->role;
    }

    /**
     * Update timestamp in login session with timestamp of current request
     *
     * Note:
     * It's necessary to update this timestamp with every single request when user
     * actively interacts with the app to make auto-logout feature working properly.
     *
     * Warning:
     * Never update timestamp before calling isLogged or isAuthorised method then
     * user would be never automatically logged out.
     */
    public function updateAutoLogoutTs(): void
    {
        if (!$this->autoLogoutTsUpdated) { // save resources and prevent repeated update
            if (session_status() != PHP_SESSION_NONE && $this->session->isInSession($this->sessionKey)) {
                $_SESSION[$this->sessionKey]['ts'] = $_SERVER['REQUEST_TIME'];
                $this->autoLogoutTsUpdated = true;
            }
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
            $this->getStorage()->deleteExpired($this->permanentLoginTime);
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
            $this->permanentLoginTime ? (int)($_SERVER['REQUEST_TIME'] + $this->permanentLoginTime) : $this->permanentLoginTime
        );
        return $tokens;
    }

    /**
     * Get selector and key stored in permanent login cookie
     * @return array
     */
    private function getPermanentCookieTokens(): array
    {
        if (!$this->cookie->isCookie($this->permanentCookieName)) {
            return [];
        }

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
     * Set user id and user role found during last login check
     * @param string|int $uid
     * @param string $role
     */
    private function setLoginCheckCredentials($uid, string $role)
    {
        $this->uid = $uid;
        $this->role = $role;
    }
}
