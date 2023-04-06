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
use TYPO3\Surf\Task\Generic\CreateSymlinksTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class CreateSymlinksTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): CreateSymlinksTask
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
        $this->assertCommandExecuted('test -e ../media || mkdir -p ../media');
        $this->assertCommandExecuted('ln -s ../media media');
    }

    /**
     * @test
     */
    public function doNotTryToCreateSymlinksIfOptionIsSetToFalse(): void
    {
        $options = [
            'createNonExistingSharedDirectories' => false
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandNotExecuted('test -e ../media || mkdir -p ../media');
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
