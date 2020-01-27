<?php
declare(strict_types=1);

namespace Webiik\StaticPage;

class StaticPage
{
    /**
     * @var string
     */
    private $dir = './_site';

    /**
     * @param string $dir
     * @throws \Exception
     */
    public function setDir(string $dir): void
    {
        if (!preg_match('/^\.\//', $dir)) {
            throw new \Exception('$dir must begin with dot and slash, for example: ./foo/bar');
        }
        $this->dir = rtrim($dir, '/');
    }

    /**
     * @param string $dir
     */
    private function setAbsoluteDir(string $dir): void
    {
        $this->dir = rtrim($dir, '/');
    }

    /**
     * @param string $data
     * @param string $uri
     * @param string $file
     */
    public function save(string $data, string $uri, string $file = 'index.html'): void
    {
        // Make dirs by URI
        $dir = $this->dir . '/' . trim($uri, '/');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        // Store static page
        file_put_contents($dir . '/' . $file, $data);
    }

    /**
     * Delete $baseDir and all it contents.
     * ! Be very careful when using this method !
     * @param bool $test Do only test of deletion, don't delete files.
     */
    public function delete(bool $test = true): void
    {
        // Check if $baseDir exists
        if (!file_exists($this->dir)) {
            echo '$dir doesn\'t exist.' . "\r\n";
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->dir,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $realPath = $file->getRealPath();
            if ($realPath == '/') {
                echo '$realPath can\'t be the root.' . "\r\n";
                break;
            }

            if ($test) {
                echo $file->isDir() ? 'Remove dir: ' : 'Remove file: ';
                echo $realPath . "\r\n";
            } else {
                $res = $file->isDir() ? rmdir($realPath) : unlink($realPath);
                if ($res === false) {
                    return;
                }
            }
        }

        if ($test) {
            echo 'Remove $dir: ' . $this->dir . "\r\n";
        } else {
            @rmdir($this->dir);
        }
    }

    /**
     * Runs deletion from CLI
     * @param array $argv
     */
    public function deleteCli(array $argv): void
    {
        if (php_sapi_name() != 'cli') {
            return;
        }

        if (!isset($argv[1])) {
            echo "Missing argument \$dir.\r\n";
            return;
        }

        if (!is_string($argv[1])) {
            echo "Invalid argument. \$dir must be a type string.\r\n";
            return;
        }

        // Get dir from $argv
        $dir = $argv[1];

        // Don't allow relative paths
        if ($dir[0] == '.' || $dir[0] != '/') {
            echo '$dir must be defined by absolute path.' . "\r\n";
            return;
        }

        // Re-format $dir
        $dir = '/' . trim($dir, '/.');

        // $dir can't be the root
        if ($dir == '/') {
            echo 'Root dir can\'t be used as $dir.' . "\r\n";
            return;
        }

        if (isset($argv[2]) && !preg_match('/^true$|^false$/', $argv[2])) {
            echo "Invalid argument. \$test must be true or false.\r\n";
            return;
        }

        $this->setAbsoluteDir($dir);
        $test = isset($argv[2]) && $argv[2] == 'false' ? false : true;
        $this->delete($test);
    }
}

// When executed from the command line
if (php_sapi_name() == 'cli') {
    $staticPage = new StaticPage();
    $staticPage->deleteCli($argv);
}
