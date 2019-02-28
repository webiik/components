<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-2-brightgreen.svg"/>
</p>

OAuth2Client
============
The OAuth2Client allows you to connect to any OAuth2 server. Just follow the procedure described in the example below.

Installation
------------
```bash
composer require webiik/oauth2client
```

Example
-------
```php
// Facebook Example

// Prepare dependencies
$chc = new \Webiik\CurlHttpClient\CurlHttpClient();

// Instantiate OAuth2 client
$oAuth2Client = new \Webiik\OAuth2Client\OAuth2Client($chc);

// Your callback URL after authorization
// OAuth2 server redirects users to this URL, after user verification
$oAuth2Client->setRedirectUri('https://127.0.0.1/webiik/');

// API endpoints
$oAuth2Client->setAuthorizeUrl('https://www.facebook.com/v3.2/dialog/oauth');
$oAuth2Client->setAccessTokenUrl('https://graph.facebook.com/v3.2/oauth/access_token');

// API credentials (create yours at https://developers.facebook.com/apps/)
$oAuth2Client->setClientId('your-client-id');
$oAuth2Client->setClientSecret('your-client-sectret');

// Make API calls...

// Define scope
$scope = [
    'email',
];

if (!isset($_GET['code'])) {
    // 1. Prepare Facebook user login link with specified scope and grand type
    echo '<a href="' . $oAuth2Client->getAuthorizeUrl($scope) . '" target="_blank">Authorize with Facebook</a><br/>';
}

if (isset($_GET['code'])) {
    // 2. Verify code to obtain user access_token
    $user = $oAuth2Client->getAccessTokenByCode();

    // 3. Verify clientId and clientSecret to obtain app access_token
    $app = $oAuth2Client->getAccessTokenByCredentials();
}

if (isset($user['access_token']) && isset($app['access_token'])) {
    // 4. User and app access_tokens are valid, user and app are authorized by Facebook
    // Access protected resources...
}
```

Configuration
-------------
Before you can connect to any OAuth2 server, you have to properly configure access credentials and endpoints.

### setClientId
```php
setClientId(string $id): void
```
**setClientId()** sets client id.
```php
$oAuth2Client->setClientId('your-client-id');
```

### setClientSecret
```php
setClientSecret(string $secret): void
```
**setClientSecret()** sets client secret.
```php
$oAuth2Client->setClientSecret('your-client-sectret');
```

### setRedirectUri
```php
setRedirectUri(string $url): void
```
**setRedirectUri()** sets redirect URI to redirect a user after authorization by OAuth2 server.
```php
$oAuth2Client->setRedirectUri('https://127.0.0.1/webiik/');
```

### setAuthorizeUrl
```php
setAuthorizeUrl(string $url): void
```
**setAuthorizeUrl()** sets URL to authorize a user by OAuth2 server.
```php
$oAuth2Client->setAuthorizeUrl('https://www.facebook.com/v3.2/dialog/oauth');
```

### setAccessTokenUrl
```php
setAccessTokenUrl(string $url): void
```
**setAccessTokenUrl()** sets URL to obtain a access token.
```php
$oAuth2Client->setAccessTokenUrl('https://graph.facebook.com/v3.2/oauth/access_token');
```

### setValidateTokenUrl
```php
setValidateTokenUrl(string $url): void
```
**setValidateTokenUrl()** sets URL to validate a access token. This endpoint is not official part of OAuth2 specifications, however Google, Facebook etc. provide it.
```php
$oAuth2Client->setValidateTokenUrl('https://graph.facebook.com/debug_token');
```

Login
-------
### getAuthorizeUrl
```php
getAuthorizeUrl(array $scope = [], string $responseType = 'code', string $state = ''): string
```
**getAuthorizeUrl()** prepares a correct link to a URL set by [setAuthorizeUrl()](#setauthorizeurl).

**Parameters**
* **scope** defines access scope of your app. Learn access scopes of individual OAuth2 servers. 
* **responseType** possible response types are code, token, id_token...
* **state** read about [state parameter](https://auth0.com/docs/protocols/oauth2/oauth-state).
```php
$link = $oAuth2Client->getAuthorizeUrl(['email'])
```

Authorization
-------------
OAuth2Client allows you to get access token by all grant types provided by OAuth2 protocol. Read more about [grant types](https://auth0.com/docs/protocols/oauth2#authorization-grant-types).

### getAccessTokenByCode
```php
getAccessTokenByCode()
```
**getAccessTokenByCode()** makes HTTP POST request to a URL set by [setAccessTokenUrl()](#setaccesstokenurl). Returns an array with token(s) on success and a string with cURL error message on error. This grant type is usually used by apps for authenticating users.
```php
$user = $oAuth2Client->getAccessTokenByCode();
```

### getAccessTokenByPassword
```php
getAccessTokenByPassword(string $username, string $password, array $scope = [])
```
**getAccessTokenByPassword()** makes HTTP POST request to a URL set by [setAccessTokenUrl()](#setaccesstokenurl). Returns an array with token(s) on success and a string with cURL error message on error. This grant type is usually used by trusted apps for authenticating users.
```php
$user = $oAuth2Client->getAccessTokenByCode();
```

### getAccessTokenByCredentials
```php
getAccessTokenByCredentials()
```
**getAccessTokenByCredentials()** makes HTTP POST request to a URL set by [setAccessTokenUrl()](#setaccesstokenurl). Returns an array with token(s) on success and a string with cURL error message on error. This grant type is usually used for server-to-server communication.
```php
$app = $oAuth2Client->getAccessTokenByCredentials();
```

### getAccessTokenByRefreshToken
```php
getAccessTokenByRefreshToken(string $refreshToken)
```
**getAccessTokenByRefreshToken()** makes HTTP POST request to a URL set by [setAccessTokenUrl()](#setaccesstokenurl). Returns an array with token(s) on success and a string with cURL error message on error. Usually you can get **$refreshToken** by setting scope **offline_access** when calling [getAuthorizeUrl()](#getauthorizeurl). Read more about [refresh_token](#https://auth0.com/docs/tokens/refresh-token/current). It's used to obtain a renewed access token.
```php
$token = $oAuth2Client->getAccessTokenByRefreshToken($refreshToken);
```

### getAccessTokenBy
```php
getAccessTokenBy(array $params)
```
**getAccessTokenBy()** makes HTTP POST request to a URL set by [setAccessTokenUrl()](#setaccesstokenurl). Returns an array with token(s) on success and a string with cURL error message on error. This method allows you to get access token by custom parameters.
```php
// Get access token by code
$user = $oAuth2Client->getAccessTokenBy([
    'redirect_uri' => 'https://127.0.0.1/webiik/',
    'grant_type' => 'authorization_code',
    'code' => $_GET['code'],
]);
```

### getTokenInfo
```php
getTokenInfo(string $inputToken, string $accessToken, bool $useGet = false)
```
**getTokenInfo()** makes HTTP POST request to a URL set by [setValidateTokenUrl()](#setvalidatetokenurl). Returns an array with token(s) on success and a string with cURL error message on error. This is not official part of OAuth2 specifications, however Google, Facebook etc. provide it.
```php
$token = $oAuth2Client->getTokenInfo($inputToken, $accessToken);
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues