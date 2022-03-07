<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task;

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
