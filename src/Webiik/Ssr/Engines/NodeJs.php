<?php
declare(strict_types=1);

namespace Webiik\Ssr\Engines;

class NodeJs implements EngineInterface
{
    // __DIR__ . '/../../frontend/build/server/'
    private $tmpDir = '';

    /**
     * Todo: Move this to Ssr.php
     * Set tmp dir to store JS files loaded by NodeJS
     * @param string $path
     */
    public function setTmpDir(string $path)
    {
        $this->tmpDir = $path;
    }

    public function render(string $uid, string $uiJs, string $envFile = '', string $envJs = ''): string
    {
        // Default result
        $res = '';

        // Check if tmp dir is set
        if (!$this->tmpDir) {
            throw new \Exception('Use setTmpDir() to set temporary directory.');
        }

        // Create temporary script file for NodeJS
        $script = $envJs . 'console.log(' . $uiJs . ');';
        $isoJsFileNode = $this->tmpDir . '/' . $uid . '.js';
        file_put_contents($isoJsFileNode, $script);

        // Execute JS
        $res .= exec('node ' . $isoJsFileNode);

        // Remove temporary script file for NodeJS
        unlink($isoJsFileNode);

        return  $res;
    }
}