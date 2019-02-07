<?php
declare(strict_types=1);

namespace Webiik\CurlHttpClient;

class CurlHttpClientRes
{
    /**
     * @var array
     */
    private $info = [];

    /**
     * @var string
     */
    private $body = '';

    /**
     * @var string|array
     */
    private $headers;

    public function __construct(array $info, string $headers, string $body)
    {
        $this->info = $info;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Get response header by header name
     * @param string $name
     * @param bool $sensitive Determine to use case sensitive header names
     * @param bool $raw Determine to return header value or whole header
     * @return bool|string False if header doesn't exist
     */
    public function header(string $name, bool $sensitive = true, bool $raw = false)
    {
        $this->processHeaders();
        return $this->getHeaderFromArr($this->headers['other'], $name, $sensitive, $raw);
    }

    /**
     * Get array of all response headers
     * @return array
     */
    public function headers(): array
    {
        $this->processHeaders();
        return $this->getHeadersFromArray($this->headers['other']);
    }

    /**
     * Get response cookie value by cookie name
     * Note: Read getHeader() doc block to get more info about parameters
     * @param string $name
     * @param bool $sensitive
     * @param bool $raw
     * @return bool|string
     */
    public function cookie(string $name, bool $sensitive = true, bool $raw = false)
    {
        $this->processHeaders();
        return $this->getHeaderFromArr($this->headers['cookie'], $name, $sensitive, $raw);
    }

    /**
     * Get array of all response cookie headers
     * @return array
     */
    public function cookies(): array
    {
        return $this->getHeadersFromArray($this->headers['cookie']);
    }

    /**
     * Get associative array of all response cookies
     * @return array
     */
    public function cookiesAssoc(): array
    {
        $this->processHeaders();
        $cookies = [];
        foreach ($this->headers['cookie'] as $key => $values) {
            $cookies[$key] = $values[1];
        }
        return $cookies;
    }

    /**
     * Get response body
     * @return string
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Get response size in bytes
     * @return int
     */
    public function size(): int
    {
        $size = $this->header('Content-Length', false);
        if ($size === false) {
            $size = $this->info['size_download'];
        }
        return (int)$size;
    }

    /**
     * Get response content type
     * @return string
     */
    public function mime(): string
    {
        return isset($this->info['content_type']) ? $this->info['content_type'] : '';
    }

    /**
     * Get response HTTP status code
     * @return int
     */
    public function statusCode(): int
    {
        return isset($this->info['http_code']) ? $this->info['http_code'] : 0;
    }

    /**
     * Get cURL error message
     * @return string
     */
    public function errMessage(): string
    {
        return isset($this->info['curlErr']) ? $this->info['curlErr'] : '';
    }

    /**
     * Get cURL error number
     * @return int
     */
    public function errNum(): int
    {
        return isset($this->info['curlErrNum']) ? $this->info['curlErrNum'] : 0;
    }

    /**
     * Check if cURL is ok
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->errNum() ? false : true;
    }

    /**
     * Get cURL info array
     * @return array
     */
    public function info(): array
    {
        return $this->info;
    }

    /**
     * Get array of all request headers
     * @return array
     */
    public function requestHeaders(): array
    {
        if (isset($this->info['request_header'])) {
            return $this->getHeadersFromArray($this->headerToArray($this->info['request_header'])['other']);
        }
        return [];
    }

    /**
     * Get array of all request cookies
     * @return array
     */
    public function requestCookies(): array
    {
        if (isset($this->info['request_header'])) {
            return $this->getHeadersFromArray($this->headerToArray($this->info['request_header'])['cookie']);
        }
        return [];
    }

    /**
     * Convert standard header to associative array
     * @param string $headers
     * @return array
     */
    private function headerToArray(string $headers): array
    {
        $headerLines = explode("\n", $headers);
        $headers = [
            'other' => [],
            'cookie' => [],
        ];
        foreach ($headerLines as $headerLine) {
            if (preg_match('/^(set-cookie)?(.*?)?:(.+)/i', $headerLine, $match)) {
                // $match[0] - Complete header
                // $match[1] - If it's not empty, it's cookie header
                // $match[2] - Header name
                // $match[3] - Header value
                if ($match[1]) {
                    // $cookie[1] - Cookie name
                    // $cookie[2] - Cookie value
                    preg_match('/^(.+?)=(.*?);/', trim($match[3]), $cookie);
                    $headers['cookie'][$cookie[1]] = [
                        $match[2],
                        $cookie[2],
                        trim($match[0]),
                    ];
                } else {
                    $headers['other'][$match[2]] = [
                        $match[2],
                        trim($match[3]),
                        trim($match[0]),
                    ];
                }
            }
        }
        return $headers;
    }

    /**
     * If headers from cURL weren't converted to array, do it
     */
    private function processHeaders(): void
    {
        if (is_string($this->headers)) {
            $this->headers = $this->headerToArray($this->headers);
        }
    }

    /**
     * Return header value on success otherwise false
     * @param array $array
     * @param string $header
     * @param bool $sensitive
     * @param bool $raw
     * @return string|bool
     */
    private function getHeaderFromArr(array $array, string $header, bool $sensitive, bool $raw = false)
    {
        $index = $raw ? 2 : 1;

        if (isset($array[$header])) {
            return $array[$header][$index];
        }

        if (!$sensitive) {
            $header = strtolower($header);
            foreach ($array as $key => $values) {
                if ($header == strtolower($key)) {
                    return $values[$index];
                }
            }
        }

        return false;
    }

    /**
     * @param array $array
     * @return array
     */
    private function getHeadersFromArray(array $array): array
    {
        $headers = [];
        foreach ($array as $values) {
            $headers[] = $values[2];
        }
        return $headers;
    }
}
