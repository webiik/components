<?php
declare(strict_types=1);

namespace Webiik\StaticPage;

class StaticPage
{
    /**
     * @var string
     */
    private $baseDir = '_site';

    /**
     * @param string $baseDir
     */
    public function setBaseDir(string $baseDir): void
    {
        $this->baseDir = trim($baseDir, '/');
    }

    /**
     * @param string $html
     * @param string $uri
     */
    public function makeStatic(string $html, string $uri): void
    {
        // Make dirs by URI
        $dir = './' . $this->baseDir . '/' . trim($uri, '/');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        // Store static page
        file_put_contents($dir . '/index.html', $html);
    }
}
