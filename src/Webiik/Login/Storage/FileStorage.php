<?php
declare(strict_types=1);

namespace Webiik\Login\Storage;

class FileStorage implements StorageInterface
{
    /**
     * Storage path
     * @var string
     */
    private $path = './';

    /**
     * Permanent identifier file extension
     * @var string
     */
    private $ext = 'wip';

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = rtrim($path, '/') . '/';
    }

    /**
     * @param string $ext
     */
    public function setExt(string $ext): void
    {
        $this->ext = ltrim($ext, '.');
    }

    /**
     * @param int|string $uid
     * @param string $selector
     * @param string $key
     * @param int $expiration
     */
    public function store($uid, string $selector, string $key, int $expiration): void
    {
        $data = serialize([
            'uid' => $uid,
            'selector' => $selector,
            'key' => $key,
            'expiration' => $expiration,
        ]);
        file_put_contents($this->getFileName($selector), $data);
    }

    /**
     * @param string $selector
     * @return array
     */
    public function get(string $selector): array
    {
        $data = [];
        $filename = $this->getFileName($selector);

        if (file_exists($filename)) {
            $data = file_get_contents($filename);
            if ($data) {
                $data = unserialize($data);
            }
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @param string $selector
     */
    public function delete(string $selector): void
    {
        @unlink($this->getFileName($selector));
    }

    /**
     * @param string $selector
     * @param int $ttl
     */
    public function updateExpiration(string $selector, int $ttl): void
    {
        $data = $this->get($selector);
        if (!$data) {
            return;
        }
        $data['expiration'] = (int)($_SERVER['REQUEST_TIME'] + $ttl);
        $this->store($data['uid'], $data['selector'], $data['key'], $data['expiration']);
    }

    /**
     * @param int $ttl
     */
    public function deleteExpired(int $ttl): void
    {
        foreach (new \DirectoryIterator($this->path) as $item) {
            if ($item->isFile() && $item->getExtension() == $this->ext) {
                if ($_SERVER['REQUEST_TIME'] - $ttl > $item->getMTime()) {
                    @unlink($item->getPathname());
                }
            }
        }
    }

    /**
     * @param string $selector
     * @return string
     */
    private function getFileName(string $selector): string
    {
        return $this->path . $selector . '.' . $this->ext;
    }
}
