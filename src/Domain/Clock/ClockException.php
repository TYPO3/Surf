<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Clock;

use TYPO3\Surf\Exception;

final class ClockException extends Exception
{
    public static function stringCouldNotBeConvertedToTimestamp(string $string): self
    {
        return new self(sprintf('The string %s could not be converted to timestamp', $string));
    }

    public static function formatCouldNotBeConvertedToTimestamp(string $format, string $time): self
    {
        return new self(sprintf('The format %s could not be converted to timestamp for time %s', $format, $time));
    }
}
