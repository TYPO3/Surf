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
     * @param string $filename
     * @param string $content
     *
     * @return bool|int
     */
    public function put($filename, $content);
}
