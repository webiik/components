<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-2-brightgreen.svg"/>
</p>

OAuth1Client
============
The OAuth1Client allows you to connect to any OAuth1 server. Just follow the procedure described in the example below. 

Installation
------------
```bash
composer require webiik/oauth1client
```

Example
-------
```php
// Twitter Example

// Prepare dependencies
$chc = new \Webiik\CurlHttpClient\CurlHttpClient();
$token = new \Webiik\Token\Token();

// Instantiate OAuth1 client
$oAuth1Client = new \Webiik\OAuth1Client\OAuth1Client($chc, $token);

// Set your callback URL
// OAuth1 server redirects users to this URL, after user verification 
$oAuth1Client->setCallbackUrl('https://127.0.0.1/webiik/');

// Set OAuth1 server endpoints
$oAuth1Client->setAuthorizeUrl('https://api.twitter.com/oauth/authenticate');
$oAuth1Client->setRequestTokenUrl('https://api.twitter.com/oauth/request_token');
$oAuth1Client->setAccessTokenUrl('https://api.twitter.com/oauth/access_token');

// Set OAuth1 server access credentials (create yours at https://developer.twitter.com/en/apps)
$oAuth1Client->setConsumerKey('your-api-key');
$oAuth1Client->setConsumerSecret('your-api-secret');

// Make API calls...
// Notice: It's a good idea to separate below steps to individual routes. 

if (!isset($_GET['oauth_verifier'])) {
    // 1. Prepare Twitter login link   
    echo '<a href="' . $oAuth1Client->getAuthorizeUrl() . '" target="_blank">Authorize with Twitter</a><br/>';
}

if (isset($_GET['oauth_verifier'])) {
    // 2. Verify oauth_token
    $accessToken = $oAuth1Client->getAccessToken();
}

if (isset($accessToken, $accessToken['oauth_token'], $accessToken['oauth_token_sercret'])) {
    // 3. oauth_token is valid, user is authorized by Twitter
    // Access protected resources...    
    $urlParameters = [
        'skip_status' => 'true',   
    ];
    $res = $oAuth1Client->get('https://api.twitter.com/1.1/account/verify_credentials.json', $accessToken['oauth_token'], $accessToken['oauth_token_secret'], $urlParameters);
    header('Content-Type: application/json');
    echo $res;
}
```

Configuration
-------------
Before you can connect to any OAuth1 server, you have to properly configure access credentials and endpoints.

### setConsumerKey
```php
setConsumerKey(string $key): void
```
**setConsumerKey()** sets consumer key.
```php
$oAuth1Client->setConsumerKey('your-api-key');
```

### setConsumerSecret
```php
setConsumerSecret(string $secret): void
```
**setConsumerSecret()** sets consumer secret.
```php
$oAuth1Client->setConsumerSecret('your-api-secret');
```

### setSignatureSecret
```php
setSignatureSecret(string $secret): void
```
**setSignatureSecret()** sets signature secret. Usually it't optional or not required. 
```php
$oAuth1Client->setSignatureSecret('your-api-signature-secret');
```

### setRequestTokenUrl
```php
setRequestTokenUrl(string $url): void
```
**setRequestTokenUrl()** sets URL to obtain a request token.
```php
$oAuth1Client->setRequestTokenUrl('https://api.twitter.com/oauth/request_token');

```

### setAuthorizeUrl
```php
setAuthorizeUrl(string $url): void
```
**setAuthorizeUrl()** sets URL to authorize a request token by user at OAuth1 server.
```php
$oAuth1Client->setAuthorizeUrl('https://api.twitter.com/oauth/authenticate');

```

### setCallbackUrl
```php
setCallbackUrl(string $url): void
```
**setCallbackUrl()** sets URL to redirect a user after authorization. 
```php
$oAuth1Client->setCallbackUrl('https://127.0.0.1/webiik/');
```

### setAccessTokenUrl
```php
setAccessTokenUrl(string $url): void
```
**setAccessTokenUrl()** sets URL to obtain a access token.
```php
$oAuth1Client->setAccessTokenUrl('https://api.twitter.com/oauth/access_token');
```

Login
-----
### getAuthorizeUrl
```php
getAuthorizeUrl($requestToken): string
```
**getAuthorizeUrl()** makes HTTP POST request to a URL set by [setRequestTokenUrl()](#setrequesttokenurl) and prepares authorized link to a URL set by [setAuthorizeUrl()](#setauthorizeurl).
```php
$link = $oAuth1Client->getAuthorizeUrl();
```

Authorization
-------------
### getAccessToken
```php
getAccessToken()
```
**getAccessToken()** makes HTTP POST request to URL set by [setAccessTokenUrl()](#setaccesstokenurl) and returns an array with 'oauth_token' on success and a string with cURL error message on error.
```php
$accessToken = $oAuth1Client->getAccessToken();
```

API Access
----------
### get
```php
get(string $url, string $oauth_token, string $oauth_token_secret, array $params = []): string
```
**get()** makes authorized HTTP GET request to OAuth1 API endpoint.
```php
$urlParameters = [
    'skip_status' => 'true',   
];
$response = $oAuth1Client->get('https://api.twitter.com/1.1/account/verify_credentials.json', $accessToken['oauth_token'], $accessToken['oauth_token_secret'], $urlParameters);
```

### post
```php
post(string $url, string $oauth_token, string $oauth_token_secret, array $params = [], array $postData = []): string
```
**post()** makes authorized HTTP POST request to OAuth1 API endpoint.
```php
$urlParameters = [
    'include_entities' => 'true',   
];
$postData = [
    'status' => 'Hello Ladies + Gentlemen, a signed OAuth request!',
];
$response = $oAuth1Client->post('https://api.twitter.com/1.1/statuses/update.json', $accessToken['oauth_token'], $accessToken['oauth_token_secret'], $urlParameters, $postData);
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues