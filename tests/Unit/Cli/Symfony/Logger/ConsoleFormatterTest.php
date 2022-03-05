<?php

declare(strict_types=1);

namespace TYPO3\Surf\Tests\Unit\Cli\Symfony\Logger;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleFormatter;

class ConsoleFormatterTest extends TestCase
{
    /**
     * @var ConsoleFormatter
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new ConsoleFormatter();
    }

    /**
     * @test
     * @dataProvider records
     */
    public function format(array $record, string $expectedOutput): void
    {
        self::assertSame($expectedOutput, $this->subject->format($record));
    }

    public function records(): array
    {
        return [
            [
                [
                    'level' => Logger::ERROR,
                    'extra' => [],
                    'context' => []
                ],
                "<error>%message%</error>\n"
            ],
            [
                [
                    'level' => Logger::NOTICE,
                    'extra' => [],
                    'context' => []
                ],
                "<comment>%message%</comment>\n"
            ],
            [
                [
                    'level' => Logger::INFO,
                    'extra' => [],
                    'context' => []
                ],
                "<info>%message%</info>\n"
            ],
            [
                [
                    'level' => Logger::DEBUG,
                    'extra' => [],
                    'context' => []
                ],
                "<debug>%message%</debug>\n"
            ],
        ];
    }
}
