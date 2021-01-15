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
use TYPO3\Surf\Task\CreateDirectoriesTask;

class CreateDirectoriesTaskTest extends BaseTaskTest
{
    protected function createTask(): CreateDirectoriesTask
    {
        return new CreateDirectoriesTask();
    }

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
}
