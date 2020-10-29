<?php

namespace TYPO3\Surf\Tests\Unit\Domain\Clock;

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
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new SystemClock();
    }

    /**
     * @test
     */
    public function currentTime(): void
    {
        self::assertIsInt($this->subject->currentTime());
    }

    /**
     * @test
     */
    public function stringToTime(): void
    {
        self::assertIsInt($this->subject->stringToTime('yesterday'));
    }

    /**
     * @test
     */
    public function stringToTimeThrowsException(): void
    {
        $this->expectException(ClockException::class);
        $this->subject->stringToTime('foobarbaz');
    }

    /**
     * @test
     */
    public function createTimestampFromFormat(): void
    {
        self::assertIsInt($this->subject->createTimestampFromFormat('d.m.Y', '20.12.2002'));
    }

    /**
     * @test
     */
    public function createTimestampFromFormatThrowsException(): void
    {
        $this->expectException(ClockException::class);

        self::assertIsInt($this->subject->createTimestampFromFormat('d.m.Y', 'foobarbaz'));
    }
}
