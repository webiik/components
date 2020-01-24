<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

StaticPage
==========
The StaticPage allows you to generate static pages from your dynamic PHP pages. It is designed to be used inside any route controller and to serve static files using the NGiNX or other web servers.

Installation
------------
```bash
composer require webiik/staticpage
```

Example
-------
To make StaticPage work, follow these steps:

1. Update your route controller(s):

    Let's say you have the method `run()` you use as the route controller.
    ```php
    public function run(): void
    {
        // Page URI
        $uri = '/foo/bar/';
        
        // Page template
        $page = '<h1>Meow World!</h1>';
    
        // Generate static files the web server will try to serve with every next request  
        $staticPage = new Webiik\StaticPage\StaticPage();
        $staticPage->makeStatic($page, $uri);
    
        // Show dynamic page when the server didn't serve the static page  
        echo $page;
    }
    ```
   
2. Update your web server configuration (NGiNX example):
* Add `/_site${uri}index.html` add the beginning of your `try_files` directive in the main `location`. It tells NGiNX to try to serve static files at first. Eg:
    ```nginx
    location / {
        try_files /_site${uri}index.html $uri $uri/ /index.php?$query_string;
    }
    ```
* Add new location block for HTML files and set new root:
    ```nginx
    # Set different root for static HTML files
    location ~ \.html$ {
        root /your/web/root/dir/_site/;
        try_files $uri =404;
    }
    ```
* Check the configuration and restart the server.

Configuration
-------------
### setBaseDir
```php
setBaseDir(string $baseDir): void
```
**set()** sets a directory where all generated static files will be stored. Default directory is **_site**.
```php
$staticPage->setBaseDir('_site');
```

Generating
----------
### makeStatic
```php
makeStatic(string $html, string $uri): void
```
**makeStatic()** creates directory structure according to **$uri** and inside it stores index.html with the content of **$html**. It is then served by the web server instead of the dynamic page.
```php
$staticPage->makeStatic('<h1>Meow World!</h1>', '/foo/bar/');
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues