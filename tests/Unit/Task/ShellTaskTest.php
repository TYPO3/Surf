<?php

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;
use TYPO3\Surf\Task\ShellTask;

class ShellTaskTest extends BaseTaskTest
{
    /**
     * @var ShellTask
     */
    protected $task;

    /**
     * @test
     */
    public function executeThrowsExceptionNoCommandGiven()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @param string $command
     * @param string $expectedCommand
     *
     * @test
     * @dataProvider commands
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     */
    public function executeSomeCommandSuccessfully($command, $expectedCommand)
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
     */
    public function rollbackReturnVoidNoRollbackCommandGiven()
    {
        $this->assertNull(
            $this->task->rollback($this->node, $this->application, $this->deployment, ['command' => 'someCommand'])
        );
    }

    /**
     * @param string $command
     * @param string $expectedCommand
     *
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     * @test
     * @dataProvider commands
     */
    public function rollbackSomeCommandSuccessfully($command, $expectedCommand)
    {
        $this->task->rollback(
            $this->node,
            $this->application,
            $this->deployment,
            ['rollbackCommand' => $command, 'command' => 'someCommand']
        );
        $this->assertCommandExecuted($expectedCommand);
    }

    /**
     * @return array
     */
    public function commands()
    {
        return [
            ['ln -s {sharedPath}', sprintf('ln -s %s', escapeshellarg('/shared'))],
        ];
    }

    /**
     * @return ShellTask
     */
    protected function createTask()
    {
        return new ShellTask();
    }
}
