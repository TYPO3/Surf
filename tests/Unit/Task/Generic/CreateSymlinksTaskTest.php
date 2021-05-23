<?php
namespace TYPO3\Surf\Tests\Unit\Task\Generic;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Task\Generic\CreateSymlinksTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Class CreateSymlinksTaskTest
 */
class CreateSymlinksTaskTest extends BaseTaskTest
{
    /**
     * @var CreateSymlinksTask
     */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @return CreateSymlinksTask
     */
    protected function createTask()
    {
        return new CreateSymlinksTask();
    }

    /**
     * per default Generic\CreateSymlinksTask uses the current release path as base directory
     * for symlink creation
     *
     * @test
     */
    public function createsSymlinkInApplicationReleasePath(): void
    {
        $options = ['symlinks' => ['media' => '../media']];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted("cd {$this->deployment->getApplicationReleasePath($this->node)}");
        $this->assertCommandExecuted('ln -s ../media media');
    }

    /**
     * if option "genericSymlinksBaseDir" is set, instead of release path, the herin given path
     * is used as  base directory
     *
     * @test
     */
    public function createsSymlinkInConfiguredBasePath(): void
    {
        $options = [
            'symlinks' => ['media' => '../media'],
            'genericSymlinksBaseDir' => '/home/foobar/data'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('cd /home/foobar/data');
        $this->assertCommandExecuted('ln -s ../media media');
    }

    /**
     * the option "symlinks" is an array of symlinks, the symlinks are created looping through the array
     *
     * @test
     */
    public function createsMultipleSymlinks(): void
    {
        $options = [
            'symlinks' => [
                'media' => '../media',
                'log' => '../log',
                'var' => '../var',
            ],
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted("cd {$this->deployment->getApplicationReleasePath($this->node)}");
        $this->assertCommandExecuted('ln -s ../media media');
        $this->assertCommandExecuted('ln -s ../log log');
        $this->assertCommandExecuted('ln -s ../var var');
    }
}
