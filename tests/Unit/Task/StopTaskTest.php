<?php
namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\StopWorkflowException;
use TYPO3\Surf\Task\StopTask;

/**
 * Unit test for the StopTask
 */
class StopTaskTest extends BaseTaskTest
{
    /**
     * @test
     */
    public function executeThrowsStopWorkflowException(): void
    {
        $this->expectException(StopWorkflowException::class);

        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     */
    public function simulateThrowsStopWorkflowException(): void
    {
        $this->expectException(StopWorkflowException::class);

        $this->task->simulate($this->node, $this->application, $this->deployment);
    }

    /**
     * @return StopTask
     */
    protected function createTask(): StopTask
    {
        return new StopTask();
    }
}
