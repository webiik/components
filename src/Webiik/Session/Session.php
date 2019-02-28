<?php
declare(strict_types=1);

namespace Webiik\Session;

class Session
{
    /**
     * Dir to save sessions. If empty, default session.save_path from php.ini is used.
     * @link http://php.net/manual/en/function.session-save-path.php
     * @var string
     */
    private $sessionDir = '';

    /**
     * Session cookie name
     * @link http://php.net/manual/en/session.configuration.php#ini.session.name
     */
    private $sessionName = 'PHPSESSID';

    /**
     * Session cookie lifetime, 0 - until the browser is closed
     * @link http://php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime
     * @var int
     */
    private $sessionCookieLifetime = 0;

    /**
     * Auto-delete(garbage collection) settings. Default is 1/100 to delete sessions older than 1440s (24m).
     * @link http://php.net/manual/en/function.setcookie.php
     */

    /**
     * @link http://php.net/manual/en/session.configuration.php#ini.session.gc-probability
     * @var int
     */
    private $sessionGcProbability = 1;

    /**
     * @link http://php.net/manual/en/session.configuration.php#ini.session.gc-divisor
     * @var int
     */
    private $sessionGcDivisor = 100;

    /**
     * @link http://php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime
     * @var int
     */
    private $sessionGcLifetime = 1440;

    /**
     * Other session cookie parameters
     * @link http://php.net/manual/en/function.setcookie.php
     */

    /**
     * @var string
     */
    private $uri = '';

    /**
     * @var string
     */
    private $domain = '';

    /**
     * @var bool
     */
    private $secure = false;

    /**
     * @var bool
     */
    private $httpOnly = false;

    /**
     * Set the (sub)domain that the cookie is available to
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * Set the path(URI) on the server in which the cookie will be available on
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * Set that the cookies should only be transmitted over a secure HTTPS connection from the client
     * @param bool $bool
     */
    public function setSecure(bool $bool): void
    {
        $this->secure = $bool;
    }

    /**
     * Set that the cookies will be accessible only through the HTTP protocol
     * @param bool $bool
     */
    public function setHttpOnly(bool $bool): void
    {
        $this->httpOnly = $bool;
    }

    /**
     * Set session name
     * @param string $name
     */
    public function setSessionName(string $name): void
    {
        $this->sessionName = $name;
    }

    /**
     * Set dir on server where sessions are stored
     * @param string $path
     */
    public function setSessionDir(string $path): void
    {
        $this->sessionDir = $path;
    }

    /**
     * @param int $sessionGcProbability
     */
    public function setSessionGcProbability(int $sessionGcProbability): void
    {
        $this->sessionGcProbability = $sessionGcProbability;
    }

    /**
     * @param int $sessionGcDivisor
     */
    public function setSessionGcDivisor(int $sessionGcDivisor): void
    {
        $this->sessionGcDivisor = $sessionGcDivisor;
    }

    /**
     * Set max time specifies the number of seconds after which session will be seen as 'garbage' and may be deleted.
     * Default value is set to 1440
     * @param int $sec
     */
    public function setSessionGcLifetime(int $sec): void
    {
        $this->sessionGcLifetime = $sec;
    }

    /**
     * Start session if it's not started and fill it with basic session values
     * Delete expired or suspicious session
     * @return bool
     */
    public function sessionStart(): bool
    {
        if (session_status() == PHP_SESSION_NONE) {
            if ($this->sessionDir) {
                session_save_path($this->sessionDir);
            }

            // Set built-in session garbage collector (auto-delete sessions)
            ini_set('session.gc_divisor', (string)$this->sessionGcDivisor);
            ini_set('session.gc_probability', (string)$this->sessionGcProbability);
            ini_set('session.gc_maxlifetime', (string)$this->sessionGcLifetime);

            session_name($this->sessionName);

            session_set_cookie_params(
                $this->sessionCookieLifetime,
                $this->uri,
                $this->domain,
                $this->secure,
                $this->httpOnly
            );

            session_start();

            // If not set, set user IP and agent to session
            $this->setBasicSessionValues();

            // If user IP or agent changed, destroy session
            if ($this->isSessionSuspicious()) {
                $this->sessionDestroy();
                return false;
            }
        }

        return true;
    }

    /**
     * Regenerate session id and delete old session
     */
    public function sessionRegenerateId(): void
    {
        $this->sessionStart();
        session_regenerate_id(true);
    }

    /**
     * Set value into session
     * @param string $key
     * @param mixed $value
     */
    public function setToSession(string $key, $value): void
    {
        $this->sessionStart();
        $_SESSION[$key] = $value;
    }

    /**
     * Add value into existing session key
     * @param string $key
     * @param mixed $value
     */
    public function addToSession(string $key, $value): void
    {
        $this->sessionStart();
        if ($this->isInSession($key)) {
            $context = is_array($_SESSION[$key]) ? $_SESSION[$key] : [$_SESSION[$key]];
            $value = is_array($value) ? $value : [$value];
            $_SESSION[$key] = array_merge_recursive($context, $value);
        } else {
            $this->setToSession($key, $value);
        }
    }

    /**
     * Return session value or false if session does not exist
     * @param string $key
     * @return bool
     */
    public function isInSession(string $key): bool
    {
        $this->sessionStart();
        return isset($_SESSION[$key]);
    }

    /**
     * Return session value or false if session does not exist
     * @param string $key
     * @return mixed
     */
    public function getFromSession(string $key)
    {
        $this->sessionStart();
        return $_SESSION[$key];
    }

    /**
     * Return all session values
     * @return mixed
     */
    public function getAllFromSession()
    {
        $this->sessionStart();
        return $_SESSION;
    }

    /**
     * Delete value from session
     * @param string $key
     */
    public function delFromSession(string $key): void
    {
        $this->sessionStart();
        $_SESSION[$key] = '';
        unset($_SESSION[$key]);
    }

    /**
     * Delete all values in session
     */
    public function dellAllFromSession(): void
    {
        $this->sessionStart();
        $_SESSION = [];
    }

    /**
     * Delete session
     */
    public function sessionDestroy(): void
    {
        $this->sessionStart();
        $this->dellAllFromSession();
        session_destroy();
        unset($_COOKIE[session_name()]);
    }

    /**
     * If not set, set basic values we use to handle every session
     */
    private function setBasicSessionValues(): void
    {
        if (!$this->isInSession('ip')) {
            $this->setToSession('ip', $_SERVER['REMOTE_ADDR']);
        }

        if (!$this->isInSession('agent') && isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->setToSession('agent', $_SERVER['HTTP_USER_AGENT']);
        }
    }

    /**
     * If user agent or IP was changed during session lifetime then session is suspicious
     */
    private function isSessionSuspicious()
    {
        if ($this->getFromSession('ip') != $_SERVER['REMOTE_ADDR']) {
            return true;
        }

        if (isset($_SERVER['HTTP_USER_AGENT']) && $this->getFromSession('agent') != $_SERVER['HTTP_USER_AGENT']) {
            return true;
        }

        return false;
    }
}
