<?php
declare(strict_types=1);

namespace Webiik\OAuth1Client;

use Webiik\CurlHttpClient\CurlHttpClient;
use Webiik\Token\Token;

class OAuth1Client
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var CurlHttpClient
     */
    private $curlHttpClient;

    /**
     * URL where a user will be redirected after authorization
     * e.g. https://localhost/social-login
     * @var string
     */
    private $oauth_callback_url = '';

    // OAuth1 API endpoints

    /**
     * URL where you obtain unauthorized request token
     * e.g. https://api.twitter.com/oauth/request_token
     * @var string
     */
    private $oauth_request_token_url = '';

    /**
     * URL where user authorize request token
     * e.g. https://api.twitter.com/oauth/authenticate
     * @var string
     */
    private $oauth_authorize_url = '';

    /**
     * URL where you obtain access token
     * e.g. https://api.twitter.com/oauth/access_token
     * @var string
     */
    private $oauth_access_token_url = '';

    // OAuth1 API access credentials

    /**
     * API key
     * @var string
     */
    private $oauth_consumer_key = '';

    /**
     * API secret
     * @var string
     */
    private $oauth_consumer_secret = '';

    // Every request to OAuth1 server requires valid signature according to OAuth1 specifications.

    /**
     * API signature secret (optional)
     * @var string
     */
    private $oauth_signature_secret = '';

    /**
     * @var string
     */
    private $oauth_signature_method = 'HMAC-SHA1';

    // OAuth version

    /**
     * @var string
     */
    private $oauth_version = '1.0';

    /**
     * @param CurlHttpClient $curlHttpClient
     * @param Token $token
     */
    public function __construct(CurlHttpClient $curlHttpClient, Token $token)
    {
        $this->token = $token;
        $this->curlHttpClient = $curlHttpClient;
    }

    /**
     * @param string $secret
     */
    public function setConsumerSecret(string $secret): void
    {
        $this->oauth_consumer_secret = $secret;
    }

    /**
     * @param string $key
     */
    public function setConsumerKey(string $key): void
    {
        $this->oauth_consumer_key = $key;
    }

    /**
     * @param string $secret
     */
    public function setSignatureSecret(string $secret): void
    {
        $this->oauth_signature_secret = $secret;
    }

    /**
     * @param string $url
     */
    public function setCallbackUrl(string $url): void
    {
        $this->oauth_callback_url = $url;
    }

    /**
     * @param string $url
     */
    public function setRequestTokenUrl(string $url): void
    {
        $this->oauth_request_token_url = $url;
    }

    /**
     * @param string $url
     */
    public function setAuthorizeUrl(string $url): void
    {
        $this->oauth_authorize_url = $url;
    }

    /**
     * @param string $url
     */
    public function setAccessTokenUrl(string $url): void
    {
        $this->oauth_access_token_url = $url;
    }

    /**
     * Return authorize URL with filled request token
     * @return string
     */
    public function getAuthorizeUrl(): string
    {
        $requestToken = $this->getRequestToken();
        $requestToken = is_string($requestToken) ? $requestToken : $requestToken['oauth_token'];
        return $this->oauth_authorize_url . '?oauth_token=' . urlencode($requestToken);
    }

    /**
     * Return array with response from OAuth provider e.g. 'oauth_token' and other data if provided
     * On cURL error return cURL error message
     * @return array|string
     */
    public function getAccessToken()
    {
        // Prepare POST data
        $oauth_verifier = isset($_GET['oauth_verifier']) ? $_GET['oauth_verifier'] : '';
        $postData = ['oauth_verifier' => $oauth_verifier];

        // Basic request header data
        $oauth_token = isset($_GET['oauth_token']) ? $_GET['oauth_token'] : '';
        $headers = $this->prepareData($oauth_token);

        // Add signature to header data
        $headers['oauth_signature'] = $this->createSignature('POST', $this->oauth_access_token_url, $this->oauth_signature_secret, $headers, [], $postData);
        $headers = $this->prepareAuthHeader($headers);

        // Send HTTP request and get response
        $req = $this->curlHttpClient->prepareRequest($this->oauth_access_token_url);
        $req->method('POST');
        $req->headers($headers);
        $req->postData($postData);
        $res = $this->curlHttpClient->send($req);
        if ($res->isOk()) {
            parse_str($res->body(), $body);
            return $body;
        }

        return $res->errMessage();
    }

    /**
     * Send authorized GET HTTP request to OAuth 1 server and return response
     * @param string $url
     * @param string $oauth_token
     * @param string $oauth_token_secret
     * @param array $params Associative array of URL parameters
     * @return string
     */
    public function get(
        string $url,
        string $oauth_token,
        string $oauth_token_secret,
        array $params = []
    ): string {
        return $this->sendAuthRequest('GET', $url, $oauth_token, $oauth_token_secret, $params);
    }

    /**
     * Send authorized POST HTTP request to OAuth 1 server and return response
     * @param string $url
     * @param string $oauth_token
     * @param string $oauth_token_secret
     * @param array $params Associative array of URL parameters
     * @param array $postData Associative array of POST data
     * @return string
     */
    public function post(
        string $url,
        string $oauth_token,
        string $oauth_token_secret,
        array $params = [],
        array $postData = []
    ): string {
        return $this->sendAuthRequest('POST', $url, $oauth_token, $oauth_token_secret, $params, $postData);
    }

    /**
     * Send authorized HTTP request to OAuth 1 server and return response
     * @param string $method
     * @param string $url
     * @param string $oauth_token
     * @param string $oauth_token_secret
     * @param array $params
     * @param array $postData
     * @return string
     */
    private function sendAuthRequest(
        string $method,
        string $url,
        string $oauth_token,
        string $oauth_token_secret,
        array $params = [],
        array $postData = []
    ): string {

        // Basic request header data
        $headers['oauth_consumer_key'] = $this->oauth_consumer_key;
        $headers['oauth_nonce'] = $this->token->generateCheap(16);
        $headers['oauth_signature_method'] = $this->oauth_signature_method;
        $headers['oauth_timestamp'] = time();
        $headers['oauth_token'] = $oauth_token;
        $headers['oauth_version'] = $this->oauth_version;

        // Add signature to header data
        $headers['oauth_signature'] = $this->createSignature($method, $url, $oauth_token_secret, $headers, $params, $postData);
        $headers = $this->prepareAuthHeader($headers);

        // Send HTTP request and get response
        $url = $params ? $url . '?' . http_build_query($params) : $url;
        $req = $this->curlHttpClient->prepareRequest($url);
        $req->headers($headers);
        if ($postData && $method == 'POST') {
            $req->postData($postData);
        }
        $res = $this->curlHttpClient->send($req);
        if ($res->isOk()) {
            return $res->body();
        }

        return $res->errMessage();
    }

    /**
     * Return array with response from OAuth provider e.g. 'oauth_token' and other data if provided
     * On cURL error return cURL error message
     * @return array|string
     */
    private function getRequestToken()
    {
        // Basic request header data
        $headers = $this->prepareData();

        // Add signature to header data
        $headers['oauth_signature'] = $this->createSignature('POST', $this->oauth_request_token_url, $this->oauth_signature_secret, $headers);
        $headers = $this->prepareAuthHeader($headers);

        // Send HTTP request and get request_token
        $req = $this->curlHttpClient->prepareRequest($this->oauth_request_token_url);
        $req->method('POST');
        $req->headers($headers);
        $res = $this->curlHttpClient->send($req);
        if ($res->isOk()) {
            parse_str($res->body(), $body);
            return $body;
        }

        return $res->errMessage();
    }

    /**
     * Prepare array of data required by OAuth1.
     * @param string $oauth_token
     * @return array
     */
    private function prepareData(string $oauth_token = ''): array
    {
        $data = [
            'oauth_callback' => $this->oauth_callback_url,
            'oauth_consumer_key' => $this->oauth_consumer_key,
            'oauth_signature_method' => $this->oauth_signature_method,
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->token->generateCheap(16),
            'oauth_version' => $this->oauth_version,
        ];

        if ($oauth_token) {
            $data['oauth_token'] = $oauth_token;
        }

        return $data;
    }


    /**
     * Generate OAuth1 signature and add it to array data
     * @param string $method
     * @param string $url
     * @param string $secret
     * @param array $headers
     * @param array $urlParams
     * @param array $postData
     * @return string
     */
    private function createSignature(
        string $method,
        string $url,
        string $secret,
        array $headers,
        array $urlParams = [],
        array $postData = []
    ): string {
        // Prepare parameters for parameter string
        $parameters = $headers + $urlParams + $postData;
        ksort($parameters);

        // Prepare parameter string
        $parameterString = '';
        foreach ($parameters as $key => $val) {
            $parameterString .= urlencode((string)$key) . '=' . urlencode((string)$val) . '&';
        }
        $parameterString = rtrim($parameterString, '&');

        // Prepare signature base string
        $baseString = strtoupper($method) . '&';
        $baseString .= urlencode($url) . '&';
        $baseString .= urlencode($parameterString);

        // Prepare signing key
        $signingKey = urlencode($this->oauth_consumer_secret) . '&' . urlencode($secret);

        // Calculate the signature
        return base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));
    }

    /**
     * Prepare OAuth request authorization header
     * @param array $data
     * @return array
     */
    private function prepareAuthHeader(array $data): array
    {
        $headers = [];
        foreach ($data as $key => $value) {
            $headers[] = urlencode((string)$key) . '="' . urlencode((string)$value) . '"';
        }
        return ['Authorization: OAuth ' . implode(', ', $headers)];
    }
}
