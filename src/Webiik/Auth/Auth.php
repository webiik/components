<?php
declare(strict_types=1);

namespace Webiik\Auth;

use Webiik\Auth\Storage\StorageInterface;
use Webiik\Cookie\Cookie;
use Webiik\Session\Session;
use Webiik\Token\Token;

class Auth
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Token
     */
    private $token;

    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * Permanent identifier Storage factory or instance after call getStorage
     * @var StorageInterface
     */
    private $storage;

    /**
     * Resolute login by sections (e.g. lang, app part, ...)
     *
     * Note:
     * To resolute login by sections, it's necessary to set current section with 'setLoginSection'
     *
     * false - login is valid for every section
     * true - login is valid only for current section
     *
     * @var bool
     */
    private $useLoginSections = false;

    /**
     * Time in sec to auto logout user on inactivity between two requests
     *
     * Note:
     * Not available for permanent login.
     * 0 - no auto logout
     *
     * @var int
     */
    private $autoLogout = 0;

    /**
     * Login session name
     * @var string
     */
    private $sessionName = 'logged';

    /**
     * Name of permanent login cookie
     * @var string
     */
    private $cookieName = 'PC';

    /**
     * Time in seconds to keep permanent cookie and identifiers alive
     *
     * Note:
     * 0 - unlimited
     *
     * @var int
     */
    private $ttl = 0;

    /**
     * All available(allowed) user roles and associated actions e.g. ['user' => ['account-read']]
     * @var array
     */
    private $authorisation = [];

    /**
     * User id from last login check using the isLogged or isAuthorised methods
     * @var int|string
     */
    private $uid;

    /**
     * User role from last login check using the isLogged or isAuthorised methods
     * @var string
     */
    private $role;

    /**
     * @param callable $factory
     */
    public function setStorage(callable $factory)
    {
        $this->storage = $factory;
    }

    /**
     * @param string $name
     */
    public function setCookieName(string $name): void
    {
        $this->cookieName = $name;
    }

    /**
     * @param int $sec
     */
    public function setTtl(int $sec): void
    {
        $this->ttl = $sec;
    }

    /**
     * @param string $name
     */
    public function setLoginSection(string $name): void
    {
        if ($this->useLoginSections) {
            $name = strtolower($name);
            $this->sessionName .= '_' . $name;
            $this->cookieName .= '_' . $name;
        }
    }

    /**
     * @param bool $bool
     */
    public function useLoginSections(bool $bool): void
    {
        $this->useLoginSections = $bool;
    }

    /**
     * @param int $sec
     */
    public function setAutoLogout(int $sec): void
    {
        $this->autoLogout = $sec;
    }

    /**
     * @param string $role
     * @param array $actions
     */
    public function setAuthorisation(string $role, array $actions = []): void
    {
        $this->authorisation[$role] = $actions;
    }

    /**
     * Log in the user
     * @param $uid
     * @param string $role
     * @param bool $permanent
     */
    public function login($uid, string $role = '', bool $permanent = false): void
    {
        $this->session->sessionRegenerateId();
        $this->session->setToSession($this->sessionName, [
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
                    $tokens['key'],
                    $this->ttl ? ($_SERVER['REQUEST_TIME'] + $this->ttl) : $this->ttl
                );
            } catch (\Exception $exception) {
                // Permanent login can't be created due to missing tokens.
                // It's not a reason to stop the app. So just ignore it and use
                // only regular login.
            }
        }
    }

    /**
     * Update timestamp of request in login session
     *
     * Note:
     * It's necessary to update this timestamp with every single request when user
     * actively interacts with the app to make auto-logout feature working properly.
     *
     * Warning:
     * Never update timestamp before calling isLogged or isAuthorised method then
     * user would be never automatically logged out.
     */
    public function updateTs(): void
    {
        if (session_status() != PHP_SESSION_NONE && $this->session->isInSession($this->sessionName)) {
            $_SESSION[$this->sessionName]['ts'] = $_SERVER['REQUEST_TIME'];
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
        if ($this->session->isInSession($this->sessionName)) {
            $loginSession = $this->session->getFromSession($this->sessionName);
            $this->setLoginCheckCredentials($loginSession['uid'], $loginSession['role']);
            $isLogged = true;
        }

        // Try to delete expired permanent login identifiers, when permanent login storage is used.
        // To delete use the same probability as is the probability to delete expired sessions.
        if ($this->storage) {
            $max = (int)ini_get('session.gc_divisor');
            $probability = (int)ini_get('session.gc_probability');
            if (mt_rand(1, $max) <= $probability) {
                $this->getStorage()->deleteExpired($this->ttl);
            }
        }

        // When it's necessary and it's available, try to get login credentials from permanent identifier
        if (!$isLogged && $this->storage && $this->cookie->isCookie($this->cookieName)) {

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
            $this->login($identifierData['uid'], $identifierData['role'], false);
            $this->setLoginCheckCredentials($identifierData['uid'], $identifierData['role']);
            $isLogged = true;
        }

        // Auto logout (not available for permanent login)
        if ($isLogged && $this->autoLogout && !$this->cookie->isCookie($this->cookieName)) {
            if ($isLogged['ts'] + $this->autoLogout < $_SERVER['REQUEST_TIME']) {
                $this->logout();
                $isLogged = false;
            }
        }

        return $isLogged;
    }

    /**
     * Check if user is authorised
     * @param string $role
     * @param array $actions
     * @return bool
     */
    public function isAuthorised(string $role, array $actions = []): bool
    {
        if (!$this->isLogged()) {
            return false;
        }

        $loginSession = $this->session->getFromSession($this->sessionName);

        // Check if user have required role
        if ($loginSession['role'] != $role) {
            return false;
        }

        // Check if role is available (allowed)
        if (!isset($this->authorisation['role'])) {
            return false;
        }

        // Check if use can do all required actions
        foreach ($actions as $action) {
            if (!in_array($action, $this->authorisation['role'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Log out the user
     */
    public function logout()
    {
        // Delete login session
        $this->session->delFromSession($this->sessionName);

        // Delete permanent login cookie and identifier
        if ($this->storage && $this->cookie->isCookie($this->cookieName)) {
            $cookieTokens = $this->getPermanentCookieTokens();
            if ($cookieTokens) {
                $this->getStorage()->delete($cookieTokens['selector']);
            }
            $this->cookie->delCookie($this->cookieName);
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
     * Get storage for permanent login identifiers
     * @return StorageInterface
     */
    private function getStorage(): StorageInterface
    {
        // Instantiate storage only once
        if (is_callable($this->storage)) {
            $storage = $this->storage;
            $this->storage = $storage();
        }
        return $this->storage;
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
            $this->cookieName,
            $tokens['selector'] . '.' . $tokens['key'],
            $this->ttl ? ($_SERVER['REQUEST_TIME'] + $this->ttl) : $this->ttl
        );
        return $tokens;
    }

    /**
     * Get selector and key stored in permanent login cookie
     * @return array
     */
    private function getPermanentCookieTokens(): array
    {
        if (!$this->cookie->isCookie($this->cookieName)) {
            return [];
        }

        $cookieVal = $this->cookie->getCookie($this->cookieName);

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
     * @param $uid
     * @param string $role
     */
    private function setLoginCheckCredentials($uid, string $role)
    {
        $this->uid = $uid;
        $this->role = $role;
    }
}
