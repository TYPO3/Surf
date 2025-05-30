<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Domain\Model\Clock;

use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Clock\ClockException;
use TYPO3\Surf\Domain\Clock\SystemClock;

class SystemClockTest extends TestCase
{
    private SystemClock $subject;

    protected function setUp(): void
    {
        $this->subject = new SystemClock();
    }

    /**
     * @test
     * @dataProvider invalidStringsCannotBeConvertedToTimestamp
     */
    public function stringCouldNotBeConvertedCorrectlyExceptionIsThrown(string $string): void
    {
        $this->expectException(ClockException::class);
        $this->subject->stringToTime($string);
    }

    /**
     * @test
     * @dataProvider validStringCanBeConvertedToTimestamp
     */
    public function stringCanBeConvertedToValidTimestamp(string $string, int $base): void
    {
        self::assertEquals(strtotime($string, $base), $this->subject->stringToTime($string, $base));
    }

    /**
     * @test
     * @dataProvider validFormatCanBeConvertedToTimestamp
     */
    public function successFullyCreateTimestampFromFormat(string $format, string $time, int $expected): void
    {
        self::assertSame($expected, $this->subject->createTimestampFromFormat($format, $time));
    }

    public function validFormatCanBeConvertedToTimestamp(): \Iterator
    {
        yield ['YmdHis', date('YmdHis', 1535216980), 1535216980];
    }

    public function validStringCanBeConvertedToTimestamp(): \Iterator
    {
        yield ['1 day ago', 1535216980];
        yield ['2 days ago', 1535216980];
        yield ['1 second ago', 1535216980];
    }

    public function invalidStringsCannotBeConvertedToTimestamp(): \Iterator
    {
        yield ['2 apples ago'];
        yield ['One second and half'];
    }
}
