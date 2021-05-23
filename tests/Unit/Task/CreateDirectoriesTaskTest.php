<?php

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\CreateDirectoriesTask;

class CreateDirectoriesTaskTest extends BaseTaskTest
{
    /**
     * @test
     * @throws InvalidConfigurationException
     */
    public function executeSuccessfully(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted(sprintf('mkdir -p %s', $this->node->getReleasesPath()));
        $this->assertCommandExecuted(sprintf('mkdir -p %s', $this->node->getSharedPath()));
        $this->assertCommandExecuted(sprintf('mkdir -p %s', $this->deployment->getApplicationReleasePath($this->node)));
        $this->assertCommandExecuted(sprintf('cd %s;ln -snf ./%s next', $this->node->getReleasesPath(), $this->deployment->getReleaseIdentifier()));
    }

    /**
     * @test
     */
    public function rollbackSuccessfully(): void
    {
        $this->task->rollback($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted(sprintf('rm %s/next', $this->node->getReleasesPath()));
        $this->assertCommandExecuted(sprintf('rm -rf %s', $this->deployment->getApplicationReleasePath($this->node)));
    }

    /**
     * @return CreateDirectoriesTask
     */
    protected function createTask()
    {
        return new CreateDirectoriesTask();
    }
}
