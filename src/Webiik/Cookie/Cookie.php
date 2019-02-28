<?php
declare(strict_types=1);

namespace Webiik\Cookie;

class Cookie
{
    /**
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
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $uri
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    public function setCookie(
        string $name,
        string $value = '',
        int $expire = 0,
        string $uri = '',
        string $domain = '',
        bool $secure = false,
        bool $httponly = false
    ): bool {
        return setcookie(
            $name,
            $value,
            $expire,
            $uri ? $uri : $this->uri,
            $domain ? $domain : $this->domain,
            $secure ? $secure : $this->secure,
            $httponly ? $httponly : $this->httpOnly
        );
    }

    /**
     * Return cookie value or false if cookie does not exist
     * @param string $name
     * @return bool
     */
    public function isCookie(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Return cookie value or false if cookie does not exist
     * @param string $name
     * @return string
     */
    public function getCookie(string $name): string
    {
        return $_COOKIE[$name];
    }

    /**
     * Delete cookie
     * @param string $name
     */
    public function delCookie($name): void
    {
        $this->setCookie($name);
        unset($_COOKIE[$name]);
    }

    /**
     * Delete all cookies
     */
    public function delCookies(): void
    {
        foreach ($_COOKIE as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            $this->delCookie($name);
        }
        $_COOKIE = [];
    }
}
