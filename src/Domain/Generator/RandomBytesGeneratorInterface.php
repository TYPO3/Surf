<?php
namespace TYPO3\Surf\Domain\Generator;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

interface RandomBytesGeneratorInterface
{

    /**
     * @param int $length
     *
     * @return string
     */
    public function generate($length);
}
