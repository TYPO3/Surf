<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task;

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\ShellTask;

class ShellTaskTest extends BaseTaskTest
{
    protected function createTask(): ShellTask
    {
        return new ShellTask();
    }

    /**
     * @test
     */
    public function executeThrowsExceptionNoCommandGiven(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     * @dataProvider commands
     */
    public function executeSomeCommandSuccessfully(string $command, string $expectedCommand): void
    {
        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['command' => $command, 'ignoreErrors' => true, 'logOutput' => true]
        );
        $this->assertCommandExecuted($expectedCommand);
    }

    /**
     * @test
     * @dataProvider commands
     */
    public function rollbackSomeCommandSuccessfully(string $command, string $expectedCommand): void
    {
        $this->task->rollback(
            $this->node,
            $this->application,
            $this->deployment,
            ['rollbackCommand' => $command, 'command' => 'someCommand']
        );
        $this->assertCommandExecuted($expectedCommand);
    }

    public function commands(): array
    {
        return [
            ['ln -s {sharedPath}', sprintf('ln -s %s', escapeshellarg('/shared'))],
        ];
    }
}
