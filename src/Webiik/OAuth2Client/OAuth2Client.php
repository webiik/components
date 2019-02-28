<?php
declare(strict_types=1);

namespace Webiik\OAuth2Client;

use Webiik\CurlHttpClient\CurlHttpClient;

class OAuth2Client
{
    /**
     * @var CurlHttpClient
     */
    private $curlHttpClient;

    /**
     * URL where a user will be redirected after authorization
     * e.g. https://localhost/social-login
     * @var string
     */
    private $oauth_redirect_uri = '';

    // OAuth2 API endpoints

    /**
     * URL where user authorizes app to obtain access code or access token
     * e.g. https://www.facebook.com/v2.8/dialog/oauth
     * @var string
     */
    private $oauth_authorize_url = '';

    /**
     * URL where you obtain authorized access token by grand type
     * e.g. https://graph.facebook.com/v2.8/oauth/access_token
     * @var string
     */
    private $oauth_access_token_url = '';

    /**
     * URL where you can check if access token is still valid
     * Notice: It's not official OAuth2 end point
     * e.g. https://graph.facebook.com/debug_token
     * @var string
     */
    private $oauth_validate_token_url = '';

    // OAuth2 API access credentials

    /**
     * API key
     * @var string
     */
    private $oauth_client_id = '';

    /**
     * API secret
     * @var string
     */
    private $oauth_client_secret = '';

    /**
     * OAuth2Client constructor.
     * @param CurlHttpClient $curlHttpClient
     */
    public function __construct(CurlHttpClient $curlHttpClient)
    {
        $this->curlHttpClient = $curlHttpClient;
    }

    /**
     * @param string $id
     */
    public function setClientId(string $id): void
    {
        $this->oauth_client_id = $id;
    }

    /**
     * @param string $secret
     */
    public function setClientSecret(string $secret): void
    {
        $this->oauth_client_secret = $secret;
    }

    /**
     * @param string $url
     */
    public function setRedirectUri(string $url): void
    {
        $this->oauth_redirect_uri = $url;
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
     * @param string $url
     */
    public function setValidateTokenUrl(string $url): void
    {
        $this->oauth_validate_token_url = $url;
    }

    /**
     * Return authorization URL
     *
     * Possible response types:
     * code - grant: Authorization Code. Response will include an authorization code.
     * token - grant: Implicit. Response will include an Access Token.
     * id_token token - grant: Implicit. Response will include an Access Token and an ID Token.
     *
     * @link https://auth0.com/docs/protocols/oauth2
     * @link https://auth0.com/docs/protocols/oauth2/oauth-state
     *
     * @param array $scope
     * @param string $responseType
     * @param string $state
     * @return string
     */
    public function getAuthorizeUrl(array $scope = [], string $responseType = 'code', string $state = ''): string
    {
        $data = [
            'client_id' => $this->oauth_client_id,
            'response_type' => $responseType,
            'scope' => implode(' ', $scope),
            'redirect_uri' => $this->oauth_redirect_uri,
        ];

        if ($state) {
            $data['state'] = $state;
        }

        return $this->oauth_authorize_url . '?' . http_build_query($data);
    }

    /**
     * Return array with access_token or string on error
     *
     * Note: Used by apps for authenticating users
     * @link https://auth0.com/docs/flows/concepts/regular-web-app-login-flow
     *
     * @return array|string
     */
    public function getAccessTokenByCode()
    {
        $data = [
            'client_id' => $this->oauth_client_id,
            'client_secret' => $this->oauth_client_secret,
            // Normally redirect_uri is not required, but for convenience when login to FB it's here
            'redirect_uri' => $this->oauth_redirect_uri,
            'grant_type' => 'authorization_code',
            'code' => isset($_GET['code']) ? $_GET['code'] : '',
        ];
        return $this->sendRequest($this->oauth_access_token_url, $data);
    }

    /**
     * Return array with access_token or string on error
     *
     * Note: Used by trusted apps for authenticating users
     * @link https://auth0.com/docs/api-auth/grant/password
     *
     * @param string $username
     * @param string $password
     * @param array $scope
     * @return array|string
     */
    public function getAccessTokenByPassword(string $username, string $password, array $scope = [])
    {
        $data = [
            'client_id' => $this->oauth_client_id,
            'client_secret' => $this->oauth_client_secret,
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
        ];

        if ($scope) {
            $data['scope'] = implode(' ', $scope);
        }

        $options = [
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_USERPWD => $username . ':' . $password,
        ];

        return $this->sendRequest($this->oauth_access_token_url, $data, $options);
    }

    /**
     * Return array with access_token or string on error
     *
     * Note: Used for server-to-server communication
     * @link https://auth0.com/docs/flows/concepts/m2m-flow
     *
     * @return array|string
     */
    public function getAccessTokenByCredentials()
    {
        $data = [
            'client_id' => $this->oauth_client_id,
            'client_secret' => $this->oauth_client_secret,
            'grant_type' => 'client_credentials',
        ];
        return $this->sendRequest($this->oauth_access_token_url, $data);
    }

    /**
     * Return array with access_token or string on error
     *
     * Note: Used to obtain a renewed access token
     * @link https://auth0.com/learn/refresh-tokens/
     *
     * @param string $refreshToken
     * @return array|string
     */
    public function getAccessTokenByRefreshToken(string $refreshToken)
    {
        $data = [
            'client_id' => $this->oauth_client_id,
            'client_secret' => $this->oauth_client_secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];
        return $this->sendRequest($this->oauth_access_token_url, $data);
    }

    /**
     * Return array with access_token or string on error
     *
     * Note: Use this for passing custom parameters to obtain access_token
     *
     * @param array $params Associative array of request parameters
     * @return array|string
     */
    public function getAccessTokenBy(array $params)
    {
        $data = [
            'client_id' => $this->oauth_client_id,
            'client_secret' => $this->oauth_client_secret,
        ];
        return $this->sendRequest($this->oauth_access_token_url, array_merge($data, $params));
    }

    /**
     * Return array with is_valid info or string on error
     *
     * Note: This is not official part of OAuth2 specifications, however Google, Facebook etc. provide it.
     *
     * @param string $inputToken
     * @param string $accessToken
     * @param bool $useGet
     * @return array|string
     */
    public function getTokenInfo(string $inputToken, string $accessToken, bool $useGet = false)
    {
        $data = [
            'input_token' => $inputToken,
            'access_token' => $accessToken,
        ];
        return $this->sendRequest($this->oauth_validate_token_url, $data, [], $useGet);
    }

    /**
     * Send HTTP request and return array on success or cURL error message on error
     * @param string $url
     * @param array $data
     * @param array $curlOptions
     * @param bool $useGet
     * @return array|string
     */
    private function sendRequest(string $url, array $data, array $curlOptions = [], bool $useGet = false)
    {
        if ($useGet) {
            $req = $this->curlHttpClient->prepareRequest($url . '?' . http_build_query($data));
        } else {
            $req = $this->curlHttpClient->prepareRequest($url);
            $req->postData($data);
        }
        $req->curlOptions($curlOptions);
        $res = $this->curlHttpClient->send($req);
        if ($res->isOk()) {
            return json_decode($res->body(), true);
        }
        return $res->errMessage();
    }
}
