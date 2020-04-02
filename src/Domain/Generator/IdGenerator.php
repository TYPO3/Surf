<?php
declare(strict_types = 1);

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
final class IdGenerator implements IdGeneratorInterface
{
    public function generate(string $prefix): string
    {
        return uniqid($prefix, false);
    }
}
