<?php

namespace TYPO3\Surf\Domain\Generator;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

/**
 * @codeCoverageIgnore
 */
class RandomBytesGenerator implements RandomBytesGeneratorInterface
{
    public function generate(int $length): string
    {
        return random_bytes($length);
    }
}
