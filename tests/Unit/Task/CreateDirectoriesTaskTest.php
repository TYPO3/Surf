<?php

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Task\CreateDirectoriesTask;

class CreateDirectoriesTaskTest extends BaseTaskTest
{
    /**
     * @test
     */
    public function executeSuccessfully(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted(sprintf('mkdir -p %s', $this->application->getReleasesPath()));
        $this->assertCommandExecuted(sprintf('mkdir -p %s', $this->application->getSharedPath()));
        $this->assertCommandExecuted(sprintf('mkdir -p %s', $this->deployment->getApplicationReleasePath($this->application)));
        $this->assertCommandExecuted(sprintf('cd %s;ln -snf ./%s next', $this->application->getReleasesPath(), $this->deployment->getReleaseIdentifier()));
    }

    /**
     * @test
     */
    public function rollbackSuccessfully(): void
    {
        $this->task->rollback($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted(sprintf('rm %s/next', $this->application->getReleasesPath()));
        $this->assertCommandExecuted(sprintf('rm -rf %s', $this->deployment->getApplicationReleasePath($this->application)));
    }

    /**
     * @return CreateDirectoriesTask
     */
    protected function createTask()
    {
        return new CreateDirectoriesTask();
    }
}
