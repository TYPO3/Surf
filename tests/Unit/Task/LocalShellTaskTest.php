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
use TYPO3\Surf\Task\LocalShellTask;

class LocalShellTaskTest extends BaseTaskTest
{
    protected function createTask(): LocalShellTask
    {
        return new LocalShellTask();
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
            ['command' => $command]
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

    public function commands(): \Iterator
    {
        yield ['ln -s {workspacePath}', sprintf('ln -s %s', escapeshellarg('./Data/Surf/TestDeployment/TestApplication'))];
    }
}
