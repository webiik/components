<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

SSR
===
Server-side rendering of javascript UI components from PHP. Out of the box, it supports React, however you can add any JS UI library. 

Prerequisites
-------------
1. [NodeJS](https://nodejs.org/en/) or [phpv8](https://github.com/phpv8/v8js).
2. Some JS bundler eg. Webpack.
3. `webiik/ssr`: 
```bash
composer require webiik/ssr
```

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
1. Create a component.

    meow.tsx
    ```tsx
    import * as React from 'react';
    
    export const Meow = (props: { name: string }) => {
        return (<h1>Meow {props.name}!</h1>);
    }
    ```
2. Register the component.

    index.ts
    ```ts
    import {registerReactComponent} from 'registerReactComponent';
    import {Meow} from 'meow';
    
    registerComponent({Meow});
    ```
3. Use your favorite [JS bundler](https://webpack.js.org) to bundle `index.ts` to `index.js`. *Remember, `index.js` MUST contain all code dependencies required to render the component with javascript.*    
4. Render the component from PHP.

    index.php
    ```php
    // Render the component on server
    $ssr = new \Webiik\Ssr\Ssr();
    $engine = new \Webiik\Ssr\Engines\NodeJs();
    $engine->setTmpDir(__DIR__);
    $ssr->useEngine($engine);
    $html = $ssr->render('index.js', 'Meow', ['name' => 'Dolly'], [
        'ssr' => true,
    ]);
   
    // Load JS libs on client 
    echo '<script src="index.js"></script>';
   
    // Print server-side rendered component on client
    echo $html;
    ```

The file structure of the example:
```console
.
..
├── node_modules
├── vendor
├── composer.json
├── package.json
├── webpack.config.js
├── tsconfig.json
├── meow.tsx
├── index.ts
├── index.js
└── index.php
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