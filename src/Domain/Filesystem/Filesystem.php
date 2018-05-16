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


}
