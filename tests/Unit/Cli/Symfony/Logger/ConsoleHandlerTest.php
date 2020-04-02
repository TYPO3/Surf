<?php

namespace TYPO3\Surf\Tests\Unit\Cli\Symfony\Logger;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleFormatter;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleHandler;

class ConsoleHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function constructor(): void
    {
        $output = $this->prophesize(OutputInterface::class);
        $handler = new ConsoleHandler($output->reveal(), false);
        $this->assertFalse($handler->getBubble(), 'the bubble parameter gets propagated');
    }

    /**
     * @test
     */
    public function isHandlingReturnsTrue(): void
    {
        $output = $this->prophesize(OutputInterface::class);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_DEBUG)->shouldBeCalled();
        $handler = new ConsoleHandler($output->reveal());
        $this->assertTrue($handler->isHandling(['level' => Logger::ERROR]));
    }

    /**
     * @test
     */
    public function isHandlingReturnsFalseIfOutputIsQuiet(): void
    {
        $output = $this->prophesize(OutputInterface::class);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_QUIET)->shouldBeCalled();
        $handler = new ConsoleHandler($output->reveal());
        $this->assertFalse($handler->isHandling(['level' => Logger::ERROR]));
    }

    /**
     * @test
     */
    public function getFormatter(): void
    {
        $output = $this->prophesize(OutputInterface::class);
        $handler = new ConsoleHandler($output->reveal());
        $this->assertInstanceOf(ConsoleFormatter::class, $handler->getFormatter());
    }
}
