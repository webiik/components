<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

StaticPage
==========
The StaticPage allows generating static content from almost any PHP app on the fly. It is designed to be used inside any route controller and to serve static files using the NGiNX or another web server.

Installation
------------
```bash
composer require webiik/staticpage
```

How To Steps
------------
To make StaticPage work, follow these two steps:

1. Update your route controller(s):

    Let's say you have the method `run()` you use as the route controller.
    ```php
    public function run(): void
    {
        // Page URI
        $uri = '/foo/bar/';
        
        // Page template
        $page = '<h1>Meow World!</h1>';
    
        // Save static file the web server will try to serve with every next request  
        $staticPage = new Webiik\StaticPage\StaticPage();
        $staticPage->save($page, $uri);
    
        // Show dynamic page when the server didn't serve the static page  
        echo $page;
    }
    ```
   
2. Update your web server configuration (NGiNX example):
* Add `/_site${uri} /_site${uri}index.html` to the beginning of your `try_files` directive in the main `location`. It tells NGiNX to try to serve static files at first. Eg:
    ```nginx
    location / {
        try_files /_site${uri} /_site${uri}index.html $uri $uri/ /index.php?$query_string;
    }
    ```
* Check the configuration and restart the server.

Configuration
-------------
### setDir
```php
setDir(string $dir): void
```
**setDir()** sets a relative path to where all generated static files will be stored. Default path is **./_site**.
```php
$staticPage->setDir('./_site');
```

Generating
----------
### save
```php
save(string $data, string $uri, string $file = 'index.html'): void
```
**save()** creates directory structure according to **$uri** and inside it saves **$file** with the content of **$data**.
```php
$staticPage->save('<h1>Meow World!</h1>', '/foo/bar/');
```

Deleting
--------
### delete
> ⚠️ Be very careful when using this method.
```php
delete(bool $test = true): void
```
**delete()** deletes content of **$dir**. When **$test** mode is set to true, it only printouts files to be deleted but doesn't delete them.
```php
$staticPage->delete();
```
This method can be called from CLI. It accept two arguments **$dir** and **$test**. 
```shell script
php StaticPage.php /absolute/path/to/static/_site true
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues