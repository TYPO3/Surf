<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Laravel;

use TYPO3\Surf\Application\Laravel;
use TYPO3\Surf\Task\Laravel\CreateDirectoriesTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class CreateDirectoriesTaskTest extends BaseTaskTest
{
    protected function createTask(): CreateDirectoriesTask
    {
        return new CreateDirectoriesTask();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new Laravel('TestApplication');
        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeWithoutArgumentsExecutesViewCacheWithoutArguments(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted("cd {$this->node->getDeploymentPath()}");
        $this->assertCommandExecuted('mkdir -p shared/storage/framework/cache/data');
        $this->assertCommandExecuted('mkdir -p shared/storage/framework/sessions');
        $this->assertCommandExecuted('mkdir -p shared/storage/framework/testing');
        $this->assertCommandExecuted('mkdir -p shared/storage/framework/views');
    }
}
