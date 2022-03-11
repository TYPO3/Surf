<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Neos\Flow;

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Task\Neos\Flow\CreateDirectoriesTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Class CreateDirectoriesTaskTest
 */
class CreateDirectoriesTaskTest extends BaseTaskTest
{
    /**
     * @var CreateDirectoriesTask
     */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): CreateDirectoriesTask
    {
        return new CreateDirectoriesTask();
    }

    /**
     * @test
     */
    public function createsDirectoriesInDeploymentRoot(): void
    {
        $options = [];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted("cd {$this->application->getDeploymentPath()}");
        $this->assertCommandExecuted('mkdir -p shared/Data/Logs');
        $this->assertCommandExecuted('mkdir -p shared/Data/Persistent');
        $this->assertCommandExecuted('mkdir -p shared/Configuration');
    }
}
