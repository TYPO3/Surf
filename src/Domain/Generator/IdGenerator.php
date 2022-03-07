<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Generator;

/**
 * @codeCoverageIgnore
 */
final class IdGenerator implements IdGeneratorInterface
{
    public function generate(string $prefix): string
    {
        return uniqid($prefix, false);
    }
}
