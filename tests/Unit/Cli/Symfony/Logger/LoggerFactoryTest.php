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
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleHandler;
use TYPO3\Surf\Cli\Symfony\Logger\LoggerFactory;

class LoggerFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function createLogger(): void
    {
        /* @var ConsoleHandler|\Prophecy\Prophecy\ObjectProphecy $output */
        $consoleHandler = $this->prophesize(ConsoleHandler::class);

        $loggerFactory = new LoggerFactory($consoleHandler->reveal());

        self::assertInstanceOf(Logger::class, $loggerFactory->createLogger());
    }
}
