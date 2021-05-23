<?php

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Task\UnlockDeploymentTask;

final class UnlockDeploymentTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function unlockSuccessfully(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted(
            sprintf('rm %s', escapeshellarg($this->node->getDeploymentPath() . '/.surf/deploy.lock'))
        );
    }

    /**
     * @test
     */
    public function unlockSuccessfullyForForceRun(): void
    {
        $this->deployment->setForceRun(true);
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted(
            sprintf('rm -f %s', escapeshellarg($this->node->getDeploymentPath() . '/.surf/deploy.lock'))
        );
    }

    /**
     * @return UnlockDeploymentTask
     */
    protected function createTask()
    {
        return new UnlockDeploymentTask();
    }
}
