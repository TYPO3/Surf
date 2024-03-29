<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Generator;

use InvalidArgumentException;

/**
 * @codeCoverageIgnore
 */
class RandomBytesGenerator implements RandomBytesGeneratorInterface
{
    public function generate(int $length): string
    {
        if ($length <= 0 || $length > PHP_INT_MAX) {
            throw new InvalidArgumentException(
                sprintf('Number must min 1 and max "%d" but "%d" given', PHP_INT_MAX, $length)
            );
        }

        return random_bytes($length);
    }
}
