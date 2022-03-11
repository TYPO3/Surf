<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Generic;

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Task\Generic\CreateDirectoriesTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

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
    public function createsDirectoriesInReleasePath(): void
    {
        $options = ['directories' => ['media']];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted("cd {$this->deployment->getApplicationReleasePath($this->application)}");
        $this->assertCommandExecuted('mkdir -p media');
    }

    /**
     * @test
     */
    public function createsDirectoriesInCustomPath(): void
    {
        $options = ['directories' => ['media'], 'baseDirectory' => '/foo/bar'];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('cd /foo/bar');
        $this->assertCommandExecuted('mkdir -p media');
    }
}
