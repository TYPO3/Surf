<?php


namespace TYPO3\Surf\Domain\Clock;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception;

final class ClockException extends Exception
{

    /**
     * @param string $string
     *
     * @return ClockException
     */
    public static function stringCouldNotBeConvertedToTimestamp($string)
    {
        return new self(sprintf('The string %s could not be converted to timestamp', $string));
    }

    /**
     * @param string $format
     * @param int $time
     *
     * @return ClockException
     */
    public static function formatCouldNotBeConvertedToTimestamp($format, $time)
    {
        return new self(sprintf('The format %s could not be converted to timestamp for time %d', $format, $time));
    }
}
