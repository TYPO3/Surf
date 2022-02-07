<?php

namespace TYPO3\Surf\Domain\Filesystem;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use UnexpectedValueException;

/**
 * @codeCoverageIgnore
 */
class Filesystem implements FilesystemInterface
{

    /**
     * @inheritDoc
     */
    public function put(string $filename, string $content)
    {
        return file_put_contents($filename, $content);
    }

    /**
     * @inheritDoc
     */
    public function get(string $filename, bool $includePath = false, $streamContext = null)
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
            return '';
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
        $matches = glob($pattern);

        if ($matches === false) {
            throw new UnexpectedValueException(sprintf('Glob pattern "%s" could be applied', $pattern));
        }

        return $matches;
    }
}
