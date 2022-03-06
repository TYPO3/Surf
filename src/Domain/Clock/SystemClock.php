<?php

declare(strict_types=1);

namespace TYPO3\Surf\Domain\Clock;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use DateTimeImmutable;

final class SystemClock implements ClockInterface
{
    public function currentTime(): int
    {
        return time();
    }

    public function stringToTime(string $string, int $time = null): int
    {
        $time ??= $this->currentTime();
        $timestamp = strtotime($string, $time);

        if ($timestamp === false) {
            throw ClockException::stringCouldNotBeConvertedToTimestamp($string);
        }

        return $timestamp;
    }

    public function createTimestampFromFormat(string $format, string $time): int
    {
        $datetime = DateTimeImmutable::createFromFormat($format, $time);

        if ($datetime === false) {
            throw ClockException::formatCouldNotBeConvertedToTimestamp($format, $time);
        }
        return $datetime->getTimestamp();
    }
}
