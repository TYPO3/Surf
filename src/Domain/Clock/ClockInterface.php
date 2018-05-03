<?php


namespace TYPO3\Surf\Domain\Clock;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

interface ClockInterface
{
    /**
     * @return int
     */
    public function currentTime();

    /**
     * @param string $string
     * @param int $time
     *
     * @return int
     */
    public function stringToTime($string, $time = null);

    /**
     * @param string $format
     * @param string $time
     *
     * @return int
     */
    public function createTimestampFromFormat($format, $time);
}
