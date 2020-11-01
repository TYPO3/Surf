<?php
namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Task\StopTask;

/**
 * Unit test for the StopTask
 */
class StopTaskTest extends BaseTaskTest
{
    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception\StopWorkflowException
     */
    public function executeThrowsStopWorkflowException()
    {
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception\StopWorkflowException
     */
    public function simulateThrowsStopWorkflowException()
    {
        $this->task->simulate($this->node, $this->application, $this->deployment);
    }

    /**
     * @return \TYPO3\Surf\Domain\Model\Task
     */
    protected function createTask()
    {
        return new StopTask();
    }
}
