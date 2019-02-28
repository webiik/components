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
        // Prepare OAuth HTTP header
        $oauth_token = isset($_GET['oauth_token']) ? $_GET['oauth_token'] : '';
        $data = $this->prepareData($oauth_token);
        $data = $this->addSignature($data, $this->oauth_access_token_url);
        $headers = $this->prepareAuthHeader($data);

        // Prepare oauth_verifier HTTP header and POST data
        $oauth_verifier = isset($_GET['oauth_verifier']) ? $_GET['oauth_verifier'] : '';
        $postData = ['oauth_verifier' => $oauth_verifier];

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
     * Return array with response from OAuth provider e.g. 'oauth_token' and other data if provided
     * On cURL error return cURL error message
     * @return array|string
     */
    private function getRequestToken()
    {
        // Prepare OAuth HTTP header
        $data = $this->prepareData();
        $data = $this->addSignature($data, $this->oauth_request_token_url);
        $headers = $this->prepareAuthHeader($data);

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

        ksort($data);

        return $data;
    }

    /**
     * Generate OAuth1 signature and add it to array data
     * @param array $data
     * @param string $url
     * @return array
     */
    private function addSignature(array $data, string $url): array
    {
        $signData = 'POST&' . urlencode($url) . '&' . urlencode(http_build_query($data));
        $signKey = urlencode($this->oauth_consumer_secret) . '&' . urlencode($this->oauth_signature_secret);
        $data['oauth_signature'] = base64_encode(hash_hmac('sha1', $signData, $signKey, true));
        return $data;
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
