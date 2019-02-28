<?php
declare(strict_types=1);

namespace Webiik\CurlHttpClient;

class CurlHttpClientReq
{
    /**
     * All set curl options
     * @var array
     */
    public $curlOptions = [];

    /**
     * CurlHttpClientReq constructor.
     * @param string $url
     */
    public function __construct(string $url)
    {
        // Set default options...
        $this->url($url);

        // Include the request headers in curl_getinfo()
        $this->curlOptions[CURLINFO_HEADER_OUT] = 1;

        $this->receiveHeaders(true);
        $this->receiveAsString(true);
    }

    // Connection settings

    /**
     * Set URL to connect to
     * @param string $url
     * @return CurlHttpClientReq
     */
    public function url(string $url): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_URL] = $url;
        return $this;
    }

    /**
     * Set connection method e.g. GET, POST...
     * @param string $method
     * @return CurlHttpClientReq
     */
    public function method(string $method): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        return $this;
    }

    /**
     * Set port to connect to
     * @param int $port
     * @return CurlHttpClientReq
     */
    public function port(int $port): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_PORT] = $port;
        return $this;
    }

    /**
     * Set to follow redirects
     * @param bool $follow
     * @param int $maxRedirs
     * @param bool $autoReferrer
     * @return CurlHttpClientReq
     */
    public function followLocation(bool $follow, int $maxRedirs = -1, bool $autoReferrer = false): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_FOLLOWLOCATION] = $follow;
        $this->curlOptions[CURLOPT_MAXREDIRS] = $maxRedirs;
        $this->curlOptions[CURLOPT_AUTOREFERER] = $autoReferrer ? 1 : 0;
        return $this;
    }

    /**
     * Set authentication credentials
     * @param string $user
     * @param string $password
     * @param int $authMethod
     * @return CurlHttpClientReq
     */
    public function auth(string $user, string $password, int $authMethod = CURLAUTH_ANY): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_HTTPAUTH] = $authMethod;
        $this->curlOptions[CURLOPT_USERPWD] = $user . ':' . $password;
        return $this;
    }

    /**
     * Set proxy to connect through
     * @param string $proxyUrl
     * @param string $user
     * @param string $password
     * @param int $authMethod
     * @return CurlHttpClientReq
     */
    public function proxy(
        string $proxyUrl,
        string $user = '',
        string $password = '',
        int $authMethod = CURLAUTH_BASIC
    ): CurlHttpClientReq {
        $this->curlOptions[CURLOPT_PROXY] = $proxyUrl;
        $this->curlOptions[CURLOPT_PROXYAUTH] = $authMethod;
        if ($user && $password) {
            $this->curlOptions[CURLOPT_PROXYUSERPWD] = $user . ':' . $password;
        }
        return $this;
    }

    /**
     * Determine to check SSL connection or not
     * @param bool $bool
     * @return CurlHttpClientReq
     */
    public function verifySSL(bool $bool): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_SSL_VERIFYHOST] = $bool ? 2 : 0;
        $this->curlOptions[CURLOPT_SSL_VERIFYPEER] = $bool ? 1 : 0;
        return $this;
    }

    /**
     * Set curl connection timeout
     * 0 - wait indefinitely
     * @param int $sec
     * @return CurlHttpClientReq
     */
    public function connectTimeout(int $sec): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_CONNECTTIMEOUT] = $sec;
        return $this;
    }

    /**
     * Set curl execution timeout
     * 0 - never quit during transfer
     * @param int $sec
     * @return CurlHttpClientReq
     */
    public function executionTimeout(int $sec): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_TIMEOUT] = $sec;
        return $this;
    }

    /**
     * Abort curl if it's slower than $bytes/sec during $sec seconds
     * @param int $bytes
     * @param int $sec
     * @return CurlHttpClientReq
     */
    public function lowSpeedLimit(int $bytes, int $sec): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_LOW_SPEED_LIMIT] = $bytes;
        $this->curlOptions[CURLOPT_LOW_SPEED_TIME] = $sec;
        return $this;
    }

    // Browser settings

    /**
     * @param string $agent
     * @return CurlHttpClientReq
     */
    public function userAgent(string $agent): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_USERAGENT] = $agent;
        return $this;
    }

    /**
     * @param string $url
     * @return CurlHttpClientReq
     */
    public function referrer(string $url): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_REFERER] = $url;
        return $this;
    }

    // Data to send

    /**
     * Add header in format e.g. Content-type: text/plain
     * @param string $header
     * @return CurlHttpClientReq
     */
    public function header(string $header): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_HTTPHEADER][] = $header;
        return $this;
    }

    /**
     * Add headers in format e.g. ['Content-type: text/plain',...]
     * @param array $headers
     * @return CurlHttpClientReq
     */
    public function headers(array $headers): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_HTTPHEADER] = $headers;
        return $this;
    }

    /**
     * @return CurlHttpClientReq
     */
    public function mimicAjax(): CurlHttpClientReq
    {
        $this->header('X-Requested-With: XMLHttpRequest');
        return $this;
    }

    /**
     * @param string $name
     * @param string $val
     * @return CurlHttpClientReq
     */
    public function cookie(string $name, string $val): CurlHttpClientReq
    {
        $this->header('Cookie: ' . $name . '=' . $val);
        return $this;
    }

    /**
     * Set cookie(s) from file
     * @param string $path
     * @return CurlHttpClientReq
     */
    public function cookieFile(string $path): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_COOKIEFILE] = $path;
        return $this;
    }

    /**
     * Catch response cookie(s) to file
     * @param string $path
     * @return CurlHttpClientReq
     */
    public function cookieJar(string $path): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_COOKIEJAR] = $path;
        return $this;
    }

    /**
     * Set curl to ignore all previous cookies
     * @return CurlHttpClientReq
     */
    public function resetCookie(): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_COOKIESESSION] = 1;
        return $this;
    }

    /**
     * @param array $fields Assoc array of post fields e.g. ['name' => 'Dolly']
     * @param array $curlFiles Assoc array of CURLFile objects e.g. ['file[0]' => new CURLFile($file, $mime, $name)]
     * @return CurlHttpClientReq
     */
    public function postData(array $fields, array $curlFiles = []): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_POST] = 1;
        $this->curlOptions[CURLOPT_SAFE_UPLOAD] = 1;
        $this->curlOptions[CURLOPT_POSTFIELDS] = array_merge($fields, $curlFiles);
        return $this;
    }

    /**
     * Upload local file to remote server
     * @param string $file
     * @param int $chunk
     * @return CurlHttpClientReq
     */
    public function upload(string $file, int $chunk = 8192): CurlHttpClientReq
    {
        $this->receiveHeaders(false);
        $this->curlOptions[CURLOPT_PUT] = 1;
        $this->curlOptions[CURLOPT_INFILE] = fopen($file, 'r');
        $this->curlOptions[CURLOPT_INFILESIZE] = filesize($file);
        $this->curlOptions[CURLOPT_READFUNCTION] = $this->readFunction($chunk);
        return $this;
    }

    /**
     * @param int $bytesSec
     * @return CurlHttpClientReq
     */
    public function uploadSpeedLimit(int $bytesSec): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_MAX_SEND_SPEED_LARGE] = $bytesSec;
        return $this;
    }

    /**
     * @param int $chunk
     * @return callable
     */
    private function readFunction(int $chunk): callable
    {
        /**
         * @param Resource $ch
         * @param Resource $stream
         * @param bool|int Bytes to be read from $stream and to be passed to cURL, so cURL can send it to server
         * @return bool|string
         */
        return function ($ch, $stream, $length = false) use ($chunk) {
            if (!$length) {
                $length = $chunk;
            }
            if (!is_resource($stream)) {
                return 0;
            }
            return fread($stream, $length);
        };
    }

    // Data to receive

    /**
     * Set response decoding
     * Supported encodings are "identity", "deflate", and "gzip".
     * If an empty string, "", is set, a header containing all
     * supported encoding types is sent.
     * @param string $encoding
     * @return CurlHttpClientReq
     */
    public function encoding(string $encoding): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_ENCODING] = $encoding;
        return $this;
    }

    /**
     * Determine to receive response body or not
     * When FALSE, request method is automatically set to HEAD
     * @param bool $bool
     * @return CurlHttpClientReq
     */
    public function receiveBody(bool $bool): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_NOBODY] = $bool ? 0 : 1;
        return $this;
    }

    /**
     * Include the headers in the curl_exec() response
     * @param bool $bool
     * @return CurlHttpClientReq
     */
    public function receiveHeaders(bool $bool): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_HEADER] = $bool ? 1 : 0;
        return $this;
    }

    /**
     * Return cURL response as a string instead of outputting it directly
     * @param bool $bool
     * @return CurlHttpClientReq
     */
    public function receiveAsString(bool $bool): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_RETURNTRANSFER] = $bool ? 1 : 0;
        return $this;
    }

    /**
     * Download remote file to local server
     * @param string $file
     * @param int $chunk
     * @return CurlHttpClientReq
     */
    public function downloadToServer(string $file, int $chunk = 8192): CurlHttpClientReq
    {
        $this->receiveHeaders(false);
        $this->curlOptions[CURLOPT_BINARYTRANSFER] = 1;
        $this->curlOptions[CURLOPT_BUFFERSIZE] = $chunk;
        $this->curlOptions[CURLOPT_WRITEFUNCTION] = $this->writeFunction($file);
        return $this;
    }

    /**
     * @param string $file
     * @return callable
     */
    private function writeFunction(string $file): callable
    {
        return function ($ch, $chunk) use ($file) {
            if (!file_put_contents($file, $chunk, FILE_APPEND)) {
                return false;
            }
            return strlen($chunk);
        };
    }

    /**
     * Stream remote file to client
     * @param int $chunk
     * @return CurlHttpClientReq
     */
    public function downloadToClient(int $chunk = 8192): CurlHttpClientReq
    {
        $this->receiveHeaders(false);
        $this->curlOptions[CURLOPT_BINARYTRANSFER] = 1;
        $this->curlOptions[CURLOPT_BUFFERSIZE] = $chunk;
        $this->curlOptions[CURLOPT_WRITEFUNCTION] = $this->streamFunction();
        return $this;
    }

    /**
     * @return callable
     */
    private function streamFunction(): callable
    {
        $this->contentLengthHeaderSet = false;
        return function ($ch, $chunk) {
            if (!$this->contentLengthHeaderSet) {
                header('Content-Length: ' . curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD));
                $this->contentLengthHeaderSet = true;
            }
            echo $chunk;
            return strlen($chunk);
        };
    }

    /**
     * Set download limit speed in bytes per second
     * @param int $bytesSec
     * @return CurlHttpClientReq
     */
    public function downloadSpeedLimit(int $bytesSec): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_MAX_RECV_SPEED_LARGE] = $bytesSec;
        return $this;
    }

    // Custom

    /**
     * Determine to get verbose info in curl response or not
     * @param bool $bool
     * @return CurlHttpClientReq
     */
    public function verbose(bool $bool): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_VERBOSE] = $bool ? 1 : 0;
        return $this;
    }

    /**
     * @link http://php.net/manual/en/function.curl-setopt.php
     * @param int $option
     * @param mixed $val
     * @return CurlHttpClientReq
     */
    public function curlOption(int $option, $val): CurlHttpClientReq
    {
        $this->curlOptions[$option] = $val;
        return $this;
    }

    /**
     * @link http://php.net/manual/en/function.curl-setopt.php
     * @param array $options
     * @return CurlHttpClientReq
     */
    public function curlOptions(array $options): CurlHttpClientReq
    {
        foreach ($options as $option => $val) {
            $this->curlOption($option, $val);
        }
        return $this;
    }

    /**
     * Write upload/download progress as a JSON to file
     *
     * Common usage:
     * To track upload/download with JS (with or without iframe)
     *
     * Notice:
     * This notice is here to prevent confusion. Progress methods in this class are
     * intended only to track curl progress. To track uploads without curl use:
     * @link http://php.net/manual/en/session.upload-progress.php
     *
     * @return CurlHttpClientReq
     * @param string $uniqueName Name of file
     * @param string $dir Directory of file
     */
    public function progressFile(string $uniqueName, string $dir): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_NOPROGRESS] = 0;
        $this->curlOptions[CURLOPT_PROGRESSFUNCTION] = $this->progressFunction($uniqueName, $dir);
        return $this;
    }

    /**
     * Print upload/download progress as a JSON (without content-type header)
     * Notice: Read withProgressFile() description to get more info
     * @return CurlHttpClientReq
     */
    public function progressJson(): CurlHttpClientReq
    {
        $this->curlOptions[CURLOPT_NOPROGRESS] = 0;
        $this->curlOptions[CURLOPT_PROGRESSFUNCTION] = $this->progressFunction();
        return $this;
    }

    /**
     * @param string $filename
     * @param string $dir
     * @return callable
     */
    private function progressFunction(string $filename = '', string $dir = ''): callable
    {
        $file = $dir && $filename ? rtrim($dir, '/') . '/' . ltrim($filename, '/') . '.log' : '';
        return function ($ch, $totalDownloadSize, $downloaded, $totalUploadSize, $uploaded) use ($file) {
            $res = [
                'downloaded' => $downloaded,
                'toDownloaded' => $totalDownloadSize,
                'uploaded' => $uploaded,
                'toUpload' => $totalUploadSize,
            ];
            if ($file) {
                file_put_contents($file, json_encode($res));
            } else {
                echo json_encode($res) . "\n";
            }
        };
    }
}
