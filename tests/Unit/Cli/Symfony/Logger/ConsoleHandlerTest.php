<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Cli\Symfony\Logger;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleFormatter;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleHandler;

class ConsoleHandlerTest extends TestCase
{
    use ProphecyTrait;
    /**
     * @test
     */
    public function constructor(): void
    {
        $output = $this->prophesize(OutputInterface::class);

        $handler = new ConsoleHandler($output->reveal(), false);
        self::assertFalse($handler->getBubble(), 'the bubble parameter gets propagated');
    }

    /**
     * @test
     */
    public function isHandlingReturnsTrue(): void
    {
        $output = $this->prophesize(OutputInterface::class);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_DEBUG)->shouldBeCalled();

        $handler = new ConsoleHandler($output->reveal());
        self::assertTrue($handler->isHandling(['level' => Logger::ERROR]));
    }

    /**
     * @test
     */
    public function isHandlingReturnsFalseIfOutputIsQuiet(): void
    {
        $output = $this->prophesize(OutputInterface::class);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_QUIET)->shouldBeCalled();

        $handler = new ConsoleHandler($output->reveal());
        self::assertFalse($handler->isHandling(['level' => Logger::ERROR]));
    }

    /**
     * @test
     */
    public function getFormatter(): void
    {
        $output = $this->prophesize(OutputInterface::class);

        $handler = new ConsoleHandler($output->reveal());
        self::assertInstanceOf(ConsoleFormatter::class, $handler->getFormatter());
    }
}
