<?php

namespace TYPO3\Surf\Domain\Filesystem;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

class Filesystem implements FilesystemInterface
{

    /**
     * @param string $filename
     * @param string $content
     *
     * @return bool|int
     */
    public function put($filename, $content)
    {
        return file_put_contents($filename, $content);
    }

    /**
     * @param string $filename
     * @param bool $includePath
     * @param resource|null $streamContext
     *
     * @return false|string
     */
    public function get($filename, $includePath = false, $streamContext = null)
    {
        return file_get_contents($filename, $includePath, $streamContext);
    }

    public function isDirectory(string $directory): bool
    {
        return is_dir($directory);
    }

    public function getTemporaryDirectory(): string
    {
        return sys_get_temp_dir();
    }

    public function getRealPath(string $path): string
    {
        $realpath = realpath($path);

        if ($realpath === false) {
            throw new \InvalidArgumentException(sprintf('Could not create realpath from path %s', $path));
        }

        return $realpath;
    }

    public function fileExists(string $file): bool
    {
        return file_exists($file);
    }

    public function createDirectory(string $directory): bool
    {
        return mkdir($directory, 0777, true);
    }

    public function glob(string $pattern): array
    {
        return glob($pattern);
    }

    public function requireFile(string $file): void
    {
        require($file);
    }
}
