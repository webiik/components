<?php
declare(strict_types=1);

namespace Webiik\Ssr;

use Webiik\Ssr\Engines\EngineInterface;

class Ssr
{
    /**
     * @var array
     */
    private $fw = [
        // key => sprintf mask of js code that renders component
        // %1$s - Name of component registered in your JS file
        // %2$s - Where rendering is performed: server, server-client, client
        // %3$s - Unique identifier of rendered component
        // %4$s - Properties of rendered component in JSON format
        'react' => 'window.WebiikReact.%1$s("%2$s", "%3$s", %4$s)',
    ];

    /**
     * @var string
     */
    private $defaultFw = 'react';

    /**
     * Engine that renders java script
     * @var EngineInterface
     */
    private $engine;

    /**
     * Dir where cache files will be stored
     * @var string
     */
    private $cacheDir = '';

    /**
     * Set which engine to use to render JS
     * @param EngineInterface $engine
     */
    public function useEngine(EngineInterface $engine): void
    {
        $this->engine = $engine;
    }

    /**
     * Set UI framework specific sprintf mask of JS code that renders UI component
     * See $this->fw and registerReactComponent.jsx to get more info
     * @param string $key
     * @param string $mask
     */
    public function setFwJsMask(string $key, string $mask): void
    {
        $this->fw[$key] = $mask;
    }

    /**
     * Set default UI framework when calling method render().
     * @param string $key
     * @throws \Exception
     */
    public function setDefaultFramework(string $key): void
    {
        if (!isset($this->fw[$key])) {
            throw new \Exception('UI framework is not set with setFwJsMask().');
        }
        $this->defaultFw = $key;
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir(string $cacheDir): void
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * Todo: Consider adding path to JS file instead of adding $routeName
     * Render component registered in your JS file
     * @param string $envFile
     * @param string $componentName
     * @param array $componentProps
     * @param array $renderOptions
     * @return string
     */
    public function render(
        string $envFile,
        string $componentName,
        array $componentProps = [],
        array $renderOptions = []
    ): string {
        // Set default response
        $res = '';

        // Default options
        $defaultOptions = [
            // Render the component on server?
            'ssr' => false,
            // Cache the component to file?
            // This must be unique key!
            // False means no cache.
            'cache' => false,
            // Cache expiration hours. 0 never expires.
            'expires' => 0,
            // UI framework
            'fw' => $this->defaultFw,
        ];

        // Set options
        $renderOptions = array_merge($defaultOptions, $renderOptions);

        // JSON encode component props
        $componentProps = json_encode($componentProps);

        // Generate component unique id
        $componentUniqueId = $componentName . uniqid();

        if ($renderOptions['ssr']) {
            // Isomorphic component
            // Render component on server and client

            // Read component from file cache
            if ($this->isCacheEnabled($renderOptions['cache'])) {
                $component = $this->readCache($renderOptions['cache'], $renderOptions['expires']);
                if ($component) {
                    return $component;
                }
            }

            // Load server-ready JS for current route
            $envJs = file_get_contents($envFile);
            $envJs = 'window = {};' . $envJs;

            // Prepare JS that renders UI component
            $uiJs = sprintf($this->fw[$renderOptions['fw']], $componentName, 'server', $componentUniqueId, $componentProps);

            // Render JS
            $res .= $this->engine->render($componentUniqueId, $uiJs, $envFile, $envJs);

            // Hydrate component on client
            $res .= '<script>';
            $res .= sprintf($this->fw[$renderOptions['fw']], $componentName, 'server-client', $componentUniqueId, $componentProps);
            $res .= '</script>';

            // Store component to file cache
            if ($this->isCacheEnabled($renderOptions['cache'])) {
                $this->writeCache($renderOptions['cache'], $res);
            }

        } else {
            // Classic component
            // Render only on client

            $res .= '<div id="' . $componentUniqueId . '"></div>';
            $res .= '<script>';
            $res .= sprintf($this->fw[$renderOptions['fw']], $componentName, 'client', $componentUniqueId, $componentProps);
            $res .= '</script>';
        }

        return $res;
    }

    /**
     * Return component from cache file or empty string
     * @param $uid
     * @param int $exp
     * @return string
     */
    private function readCache($uid, int $exp): string
    {
        $this->createCacheDir();
        $cacheFile = $this->getCacheFile($uid);

        // If component is already stored in cache file...
        if (!file_exists($cacheFile)) {
            return  '';
        }

        // ...and cache file is not expired...
        if ((time() + ($exp * 3600)) < filemtime($cacheFile)) {
            unlink($cacheFile);
            return  '';
        }

        // ...return the component from cache file.
        $component = file_get_contents($cacheFile);
        return is_string($component) ? $component : '';
    }

    /**
     * Write component to cache file
     * @param $uid
     * @param string $data
     */
    private function writeCache($uid, string $data): void
    {
        $this->createCacheDir();
        $cacheFile = $this->getCacheFile($uid);
        file_put_contents($cacheFile, $data);
    }

    /**
     * Prepare cache filename incl. path
     * @param string $uid
     * @return string
     */
    private function getCacheFile(string $uid): string
    {
        return $this->cacheDir . '/' . strtolower($uid) . '.html';
    }

    /**
     * Create cache dir, if it doesn't exist
     */
    private function createCacheDir(): void
    {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Check if cache is enabled
     * @param $cache
     * @return bool
     */
    private function isCacheEnabled($cache): bool
    {
        return $this->cacheDir && $cache && is_string($cache);
    }
}
