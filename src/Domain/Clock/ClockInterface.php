<?php

declare(strict_types=1);

namespace TYPO3\Surf\Domain\Clock;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

interface ClockInterface
{
    public function currentTime(): int;

    public function stringToTime(string $string, int $time = null): int;

    public function createTimestampFromFormat(string $format, string $time): int;
}
