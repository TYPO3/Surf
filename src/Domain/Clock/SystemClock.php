<?php


namespace TYPO3\Surf\Domain\Clock;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use DateTime;

final class SystemClock implements ClockInterface
{
    /**
     * @return int
     */
    public function currentTime()
    {
        return time();
    }

    /**
     * @param string $string
     * @param int $time
     *
     * @return false|int
     * @throws ClockException
     */
    public function stringToTime($string, $time = null)
    {
        $timestamp = strtotime($string, $time);

        if ($timestamp === false) {
            throw ClockException::stringCouldNotBeConvertedToTimestamp($string);
        }

        return $timestamp;
    }

    /**
     * @param string $format
     * @param string $time
     *
     * @return int
     */
    public function createTimestampFromFormat($format, $time)
    {
        $datetime = DateTime::createFromFormat($format, $time);

        if ($datetime === false) {
            throw ClockException::formatCouldNotBeConvertedToTimestamp($format, $time);
        }
        return $datetime->format('U');
    }
}
