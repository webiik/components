<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

SSR
===
Server-side rendering of javascript UI components from PHP. Out of the box, it supports React, however you can add any JS UI library. 

Prerequisites
-------------
1. [Composer](https://getcomposer.org)
2. [NodeJS](https://nodejs.org/en/)
3. (optional) [PHPV8JS](https://github.com/phpv8/v8js)
4. Some JS bundler eg. [Webpack](https://webpack.js.org)

Supports
--------
JS engines:
- [NodeJS](https://nodejs.org/en/)
- [PHP v8js](https://github.com/phpv8/v8js)
- Custom JS engines.

UI libraries:
- [React](https://reactjs.org)
- Custom UI library.

Step-by-step Example
--------------------
This example uses Webpack to bundle JS. You can use your favorite JS bundler. 

1. Create the `MyTest` folder with the following file structure.
    ```console
    .
    ..
    ├── package.json
    ├── webpack.config.js
    ├── meow.jsx
    ├── index.js
    └── index.php
    ```
   
2. Inside `MyTest` folder install all necessary packages. 
    ```shell script   
    npm install react
    npm install react-dom
    npm install @webiik/render-js-components
    composer require webiik/ssr      
    ```

3. Create a component.

    Edit `meow.jsx` to:
    ```jsx
    import * as React from 'react';
    
    export const Meow = (props) => {
        return (<h1>Meow {props.name}!</h1>);
    }
    ```
   
4. Register the component.

    Edit `index.js` to:
    ```js
    import {registerReactComponent} from '@webiik/render-js-components';
    import {Meow} from 'meow';
    
    registerReactComponent({Meow});
    ```
5. Configure Webpack.

    Edit `webpack.config.js` to:
    ```js
    const webpack = require('webpack');
    const path = require('path');
    module.exports = {
        entry: {
            'index': 'index.js'
        },
        output: {
            filename: '[name].js',
            path: path.resolve(__dirname + '/build/')
        },
        resolve: {
            extensions: ['.js', '.jsx']
        }        
    };      
    ```
6. Bundle `index.js` to `build/index.js`. *Remember, `build/index.js` MUST contain all code dependencies required to render the component with javascript.*
    ```shell script
    webpack -p --colors --progress     
    ```    
7. Render the component from PHP.

    Edit `index.php` to:
    ```php
    // Render the component on server
    $ssr = new \Webiik\Ssr\Ssr();
    $engine = new \Webiik\Ssr\Engines\NodeJs();
    $engine->setTmpDir(__DIR__);
    $ssr->useEngine($engine);
    $html = $ssr->render('build/index.js', 'Meow', ['name' => 'Dolly'], [
        'ssr' => true,
    ]);
   
    // Load JS libs on client 
    echo '<script src="build/index.js"></script>';
   
    // Print server-side rendered component on client
    echo $html;
    ```

Cache
-----
You can use cache to store rendered components.  
```php    
// To enable cache you MUST set cache dir and add 'cache' key to $renderOptions.
// To prevent cache conflicts cache key MUST be unique.
$ssr->setCacheDir(__DIR__);
$html = $ssr->render('index.js', 'Meow', ['name' => 'Dolly'], [
    'ssr' => true,
    'cache' => 'Meow',
    'expires' => 1, // 1 = one hour, 0 = never expires
]);   
```

Custom UI library
-----------------
SSR supports React out of the box, however, you can add support for your favorite UI library.

1. JS - Create a component registrar. Use [registerReactComponent.tsx](Js/src/registerReactComponent.tsx) as template. The purpose of component registrar is to register function that renders component on a server and client.

2. PHP - Use method `setFwJsMask()` to tell SSR how to call the component registrar from step 1.
    ```php
    $ssr->setFwJsMask('vue', 'window.WebiikVue.%1$s("%2$s", "%3$s", "%4$s")');
    ```
3. PHP - Tell SSR that you want to use the component registrar from step 2.
    ```php
    $ssr->setDefaultFramework('vue');
    ```
    or
    ```php
    $html = $ssr->render('index.js', 'Meow', ['name' => 'Dolly'], [
        'fw' => 'vue',
    ]);
    ```
> If you need it. You can use more UI libraries at once. 

Custom JS engine
----------------
The engine is a PHP class that processes JS and returns the result as a string. The engine MUST implement [EngineInterface](Engines/EngineInterface.php). See the code of [current engines](Engines) to learn more.  

1. PHP - Create your engine.
2. PHP - Tell SSR to use your engine. 
    ```php
    $ssr->useEngine(new YourCustomEngine());
    ```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues