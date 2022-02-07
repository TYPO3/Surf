<?php
namespace TYPO3\Surf\Domain\Filesystem;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

interface FilesystemInterface
{

    /**
     * @return bool|int
     */
    public function put(string $filename, string $content);

    /**
     * @param resource|null $streamContext
     *
     * @return false|string
     */
    public function get(string $filename, bool $includePath = false, $streamContext = null);

    public function isDirectory(string $directory): bool;

    public function getTemporaryDirectory(): string;

    public function getRealPath(string $path): string;

    public function fileExists(string $file): bool;

    public function createDirectory(string $directory): bool;

    public function glob(string $pattern): array;
}
