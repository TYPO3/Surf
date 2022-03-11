<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task;

use TYPO3\Surf\Task\UnlockDeploymentTask;

final class UnlockDeploymentTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): UnlockDeploymentTask
    {
        return new UnlockDeploymentTask();
    }

    /**
     * @test
     */
    public function unlockSuccessfully(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted(
            sprintf('rm %s', escapeshellarg($this->application->getDeploymentPath() . '/.surf/deploy.lock'))
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
            sprintf('rm -f %s', escapeshellarg($this->application->getDeploymentPath() . '/.surf/deploy.lock'))
        );
    }
}
