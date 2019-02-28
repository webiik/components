<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

CurlHttpClient
==============
The CurlHttpClient allows to easily send HTTP request via cURL.

Installation
------------
```bash
composer require webiik/curlhttpclient
```

Example
-------
```php
$chc = new \Webiik\CurlHttpClient\CurlHttpClient();

// Prepare simple GET request (CurlHttpClientReq)
$request = $chc->prepareRequest('https://www.google.com');

// Send request and receive response (CurlHttpClientRes)
$response = $chc->send($req);
```

Summary
-------
* [Client](#curlhttpclient)
* [Preparing Requests](#curlhttpclientreq)
* [Processing Responses](#curlhttpclientres)

CurlHttpClient
--------------
CurlHttpClient prepares cURL requests represented by CurlHttpClientReq, sends cURL requests and receives cURL responses represented by CurlHttpClientRes. It allows you to send asynchronously multiple requests at once.
### prepareRequest
```php
prepareRequest(string $url): CurlHttpClientReq
```
**prepareRequest()** creates object [CurlHttpClientReq](#curlhttpclientreq) which represents set of cURL options.
```php
$request = $chc->prepareRequest('https://www.google.com');
```

### send
```php
send(CurlHttpClientReq $req): CurlHttpClientRes
```
**send()** sends cURL request using the options defined in CurlHttpClientReq, returns [CurlHttpClientRes](#curlhttpclientres).  
```php
$response = $chc->send($req);
```

### sendMulti
```php
sendMulti(array $requests): array
```
**sendMulti()** sends asynchronously multiple cURL requests at once. Once all requests are completed, it receives array of CurlHttpClientRes.
```php
// Prepare multiple requests
$requests = [
    $chc->prepareRequest('https://www.google.com'),
    $chc->prepareRequest('https://duck.com'),
];

// Send asynchronously multiple requests at once
// Once all requests are completed, get their responses
$responses = $chc->sendMulti($requests);

// Iterate responses
foreach ($responses as $res) {
    /** @var \Webiik\CurlHttpClient\CurlHttpClientRes $res */
}
```

CurlHttpClientReq
-----------------
CurlHttpClientReq represents set of cURL options. It provides many methods to easily add most common cURL options.

### Connection settings
***
### url
```php
url(string $url): CurlHttpClientReq
```
**url()** sets URL to connect to.
```php
$request->url('https://www.google.com');
```

### method
```php
method(string $method): CurlHttpClientReq
```
**method()** sets connection method e.g. GET, POST...
```php
$request->method('POST');
```

### port
```php
port(int $port): CurlHttpClientReq
```
**port()** sets port to connect to.
```php
$request->port('1212');
```

### followLocation
```php
followLocation(bool $follow, int $maxRedirs = -1, bool $autoReferrer = false): CurlHttpClientReq
```
**followLocation()** sets to follow redirects.   
```php
$request->followLocation(true);
```

### auth
```php
auth(string $user, string $password, int $authMethod = CURLAUTH_ANY): CurlHttpClientReq
```
**auth()** sets authentication credentials.  
```php
$request->auth('user', 'password');
```

### proxy
```php
proxy(string $proxyUrl, string $user = '', string $password = '', int $authMethod = CURLAUTH_BASIC): CurlHttpClientReq
```
**proxy()** set proxy to connect through.  
```php
$request->proxy('socks5://xxx.xxx.xxx.xxx', 'user', 'password');
```

### verifySSL
```php
verifySSL(bool $bool): CurlHttpClientReq
```
**verifySSL()** sets to check SSL connection or not.
```php
$request->verifySSL(false);
```

### connectTimeout
```php
connectTimeout(int $sec): CurlHttpClientReq
```
**connectTimeout()** sets cURL connection timeout. 0 - wait indefinitely.   
```php
$request->connectTimeout(1);
```

### executionTimeout
```php
executionTimeout(int $sec): CurlHttpClientReq
```
**executionTimeout()** set cURL transfer timeout. 0 - never quit during transfer.
```php
$request->executionTimeout(1);
```

### lowSpeedLimit
```php
lowSpeedLimit(int $bytes, int $sec): CurlHttpClientReq
```
**lowSpeedLimit()** disconnects cURL if it's slower than **$bytes**/sec for **$sec** seconds.
```php
// Disconnect when cURL connection speed is lower than 128KB for 10 seconds
$request->lowSpeedLimit(1024 * 128, 10);
```

### Data To Send
***
### userAgent
```php
userAgent(string $agent): CurlHttpClientReq
```
**userAgent()** sets user agent HTTP header.
```php
$request->userAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.3 Safari/605.1.15');
```

### referrer
```php
referrer(string $url): CurlHttpClientReq
```
**referrer()** sets referrer HTTP header.
```php
$request->referrer('https://www.google.com');
```

### header
```php
header(string $header): CurlHttpClientReq
```
**header()** sets HTTP header in format e.g. 'Content-type: image/jpeg'.
```php
$request->header('Content-Type: image/jpeg');
```

### headers
```php
headers(array $headers): CurlHttpClientReq
```
**headers()** sets array of HTTP headers in format e.g. \['Content-type: image/jpeg',...\]
```php
$request->header([
    'Content-Disposition: attachment; filename="cute-cat.jpg"',
    'Content-Type: image/jpeg',
]);
```

### mimicAjax
```php
mimicAjax(): CurlHttpClientReq
```
**mimicAjax()** sets HTTP header to mimic ajax.
```php
$request->mimicAjax();
```

### cookie
```php
cookie(string $name, string $val): CurlHttpClientReq
```
**cookie()** sets 'Cookie' HTTP header.
```php
$request->cookie('cat', 'Tom');
```

### cookieFile
```php
cookieFile(string $path): CurlHttpClientReq
```
**cookieFile()** sets cookie(s) from file.  
```php
$request->cookieFile('cookies.txt');
```

### cookieJar
```php
cookieJar(string $path): CurlHttpClientReq
```
**cookieJar()** catches response cookie(s) to file.  
```php
$request->cookieJar('cookies.txt');
```

### resetCookie
```php
resetCookie(): CurlHttpClientReq
```
**resetCookie()** sets cURL to ignore all previous cookies.  
```php
$request->resetCookie();
```

### postData
```php
postData(array $fields, array $curlFiles = []): CurlHttpClientReq
```
**postData()** adds post data to cURL request.    
```php
$request->postData($_POST, $_FILES);
```

### upload
```php
upload(string $file, int $chunk = 8192): CurlHttpClientReq
```
**upload()** sets cURL to upload local file to remote server. **$file** is local file to be uploaded to remote server. 
```php
// Set address to upload to
$request = $chc->prepareRequest('ftp://yourftp.tld');

// Set auth credentials (when required)
$request->auth('user', 'password');

// Set local file to upload
$request->upload('cute-cat.jpg');

// Init uploading
$chc->send($request);
```

### uploadSpeedLimit
```php
uploadSpeedLimit(int $bytesSec): CurlHttpClientReq
```
**uploadSpeedLimit()** sets max upload speed in bytes per second.  
```php
// Limit upload speed to 1 MB/s
$request->uploadSpeedLimit(1024 * 1024);
```

### Data To Receive
***
### encoding
```php
encoding(string $encoding): CurlHttpClientReq
```
**encoding()** sets response encoding. Supported encodings are "identity", "deflate", and "gzip".   
```php
$request->encoding('deflate');
```

### receiveBody
```php
receiveBody(bool $bool): CurlHttpClientReq
```
**receiveBody()** determines to receive response body or not.  
```php
$request->receiveBody(true);
```

### receiveHeaders
```php
receiveHeaders(bool $bool): CurlHttpClientReq
```
**receiveHeaders()** determines to receive response headers or not.  
```php
$request->receiveHeaders(true);
```

### receiveAsString
```php
receiveAsString(bool $bool): CurlHttpClientReq
```
**receiveAsString()** sets cURL to return cURL response as a string instead of outputting it directly.   
```php
$request->receiveAsString(true);
```

### downloadToServer
```php
downloadToServer(string $file, int $chunk = 8192): CurlHttpClientReq
```
**downloadToServer()** sets cURL to download remote file to local server. **$file** is used as a storage of remote file.   
```php
// Set remote file to download
$request = $chc->prepareRequest('https://domain.tld/cute-cat.jpg');

// Set local file to download to
$request->downloadToServer('cute-cat.jpg');

// Init downloading
$chc->send($request);
```

### downloadToClient
```php
downloadToClient(int $chunk = 8192): CurlHttpClientReq
```
**downloadToClient()** sets cURL to stream remote file to client.
```php
// Set remote file to stream
$request = $chc->prepareRequest('https://domain.tld/cute-cat.jpg');

// Tell cURL to stream remote file to client
$request->downloadToClient();

// Set appropriate headers
header('Content-Disposition: attachment; filename="cute-cat.jpg"');
header('Content-Type: image/jpeg');

// Init streaming
$chc->send($request);
```

### downloadSpeedLimit
```php
downloadSpeedLimit(int $bytesSec): CurlHttpClientReq
```
**downloadSpeedLimit()** sets max download speed in bytes per second.
```php
// Limit download speed to 1 MB/s
$request->downloadSpeedLimit(1024 * 1024);
```

### Custom
***
### verbose
```php
verbose(bool $bool): CurlHttpClientReq
```
**verbose()** sets cURL to receive verbose response info.   
```php
$request->verbose(true);
```

### curlOption
```php
curlOption(int $option, $val): CurlHttpClientReq
```
**curlOption()** sets a cURL option.   
```php
$request->curlOption(CURLOPT_TCP_NODELAY, 1);
```

### curlOptions
```php
curlOptions(array $options): CurlHttpClientReq
```
**curlOptions()** sets an array of cURL options. 
```php
$request->curlOptions([
	CURLOPT_TCP_NODELAY => 1,
	CURLOPT_FORBID_REUSE => 1,
]);
```

### progressFile
```php
progressFile(string $uniqueName, string $dir): CurlHttpClientReq
```
**progressFile()** sets a file to store download/upload progress to. Progress is stored in JSON format. 
```php
$request->progressFile('fu37icnj', __DIR__);
```

### progressJson
```php
progressJson(): CurlHttpClientReq
```
**progressJson()** sets cURL to print upload/download progress as a JSON (without content-type header).   
```php
$request->progressJson();
```

CurlHttpClientRes
-----------------
CurlHttpClientRes represents cURL response. It provides methods to easily access most common response informations.

### header
```php
header(string $name, bool $sensitive = true, bool $raw = false)
```
**header()** gets response header by header name.

**Parameters**
* **name** header name
* **sensitive** determines if header name is case sensitive
* **raw** determines to return only header value(false) or complete header(true) 
```php
$header = $response->header('Content-Type');
```

### headers
```php
headers(): array
```
**headers()** gets array of all response headers.
```php
$headers = $response->headers();
```

### cookie
```php
cookie(string $name, bool $sensitive = true, bool $raw = false)
```
**cookie()** gets response cookie value by cookie name.

**Parameters**
* **name** cookie name
* **sensitive** determines if cookie name is case sensitive
* **raw** determines to return only cookie value(false) or complete cookie header(true)
```php
$cookie = $response->cookie('cat');
```

### cookies
```php
cookies(): array
```
**cookies()** gets array of all response cookie headers.
```php
$cookies = $response->cookies();
```

### cookiesAssoc
```php
cookiesAssoc(): array
```
**cookiesAssoc()** gets associative array of all response cookies.
```php
$cookies = $response->cookiesAssoc();
```

### body
```php
body(): string
```
**body()** gets response body.
```php
$body = $response->body();
```

### size
```php
size(): int
```
**size()** gets response size in bytes.
```php
$size = $response->size();
```

### mime
```php
mime(): string
```
**mime()** gets response content type.
```php
$mime = $response->mime();
```

### statusCode
```php
statusCode(): int
```
**statusCode()** gets response HTTP status code.
```php
$httpStatusCode = $response->statusCode();
if ($httpStatusCode == 200) {
    // Remote page responded with HTTP status OK
}
```

### errMessage
```php
errMessage(): string
```
**errMessage()** gets cURL error message.
```php
$curlErrMessage = $response->errMessage();
```

### errNum
```php
errNum(): int
```
**errNum()** gets cURL error number.
```php
$curlErrNum = $response->errNum();
if (!$curlErrNum) {
    // Curl request is OK
}
```

### isOk
```php
isOk(): bool
```
**isOk()** indicates if cURL request was ok.
```php
if ($response->isOk()) {
    // Curl request is OK
}
```

### info
```php
info(): array
```
**info()** gets cURL info array.
```php
$curlInfo = $response->info();
```

### requestHeaders
```php
requestHeaders(): array
```
**requestHeaders()** gets array of all request headers.
```php
$requestHeaders = $response->requestHeaders();
```

### requestCookies
```php
requestCookies(): array
```
**requestCookies()** gets array of all request cookie headers.
```php
$requestCookies = $response->requestCookies();
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues