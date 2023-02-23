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
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleHandler;
use TYPO3\Surf\Cli\Symfony\Logger\LoggerFactory;

class LoggerFactoryTest extends TestCase
{
    use ProphecyTrait;
    /**
     * @test
     */
    public function createLogger(): void
    {
        $consoleHandler = $this->prophesize(ConsoleHandler::class);

        $loggerFactory = new LoggerFactory($consoleHandler->reveal());

        self::assertInstanceOf(Logger::class, $loggerFactory->createLogger());
    }
}
