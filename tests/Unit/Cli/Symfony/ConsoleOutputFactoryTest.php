<?php

namespace TYPO3\Surf\Tests\Unit\Cli\Symfony;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use TYPO3\Surf\Cli\Symfony\ConsoleOutputFactory;

class ConsoleOutputFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function createOutput(): void
    {
        $consoleOutputFactory = new ConsoleOutputFactory();
        self::assertInstanceOf(ConsoleOutput::class, $consoleOutputFactory->createOutput());
    }
}
