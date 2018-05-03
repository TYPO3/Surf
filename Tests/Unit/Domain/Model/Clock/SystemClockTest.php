<?php

namespace TYPO3\Surf\Tests\Unit\Domain\Model\Clock;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Clock\ClockException;
use TYPO3\Surf\Domain\Clock\SystemClock;

class SystemClockTest extends TestCase
{

    /**
     * @var SystemClock
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new SystemClock();
    }

    /**
     * @param string $string
     *
     * @test
     * @dataProvider invalidStringsCannotBeConvertedToTimestamp
     */
    public function stringCouldNotBeConvertedCorrectlyExceptionIsThrown($string)
    {
        $this->expectException(ClockException::class);
        $this->subject->stringToTime($string);
    }

    /**
     * @param string $string
     * @param int $base
     * @test
     * @dataProvider validStringCanBeConvertedToTimestamp
     */
    public function stringCanBeConvertedToValidTimestamp($string, $base)
    {
        $this->assertEquals(strtotime($string, $base), $this->subject->stringToTime($string, $base));
    }

    /**
     * @param string $format
     * @param int $time
     * @param int $expected
     * @test
     * @dataProvider validFormatCanBeConvertedToTimestamp
     */
    public function successFullyCreateTimestampFromFormat($format, $time, $expected)
    {
        $this->assertEquals($expected, $this->subject->createTimestampFromFormat($format, $time));
    }

    /**
     * @return array
     */
    public function validFormatCanBeConvertedToTimestamp()
    {
        return [
            ['YmdHis', strftime('%Y%m%d%H%M%S', 1535216980), 1535216980],
        ];
    }

    /**
     * @return array
     */
    public function validStringCanBeConvertedToTimestamp()
    {
        return [
            ['1 day ago', 1535216980],
            ['2 days ago', 1535216980],
            ['1 second ago', 1535216980],
        ];
    }

    /**
     * @return array
     */
    public function invalidStringsCannotBeConvertedToTimestamp()
    {
        return [
            ['2 apples ago'],
            ['One second and half'],
        ];
    }
}
