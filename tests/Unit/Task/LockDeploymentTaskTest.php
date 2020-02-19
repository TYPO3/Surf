<?php

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\DeploymentLockedException;
use TYPO3\Surf\Task\LockDeploymentTask;

final class LockDeploymentTaskTest extends BaseTaskTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function lockSuccessfully()
    {
        $testIfDeploymentLockFileExists = sprintf('if [ -f %s ]; then echo 1; else echo 0; fi', escapeshellarg($this->application->getDeploymentPath() . '/.surf/deploy.lock'));
        $this->responses = [
            $testIfDeploymentLockFileExists => false,
        ];
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted($testIfDeploymentLockFileExists);
    }

    /**
     * @test
     */
    public function deploymentIsLockedThrowsException()
    {
        $testIfDeploymentLockFileExists = sprintf('if [ -f %s ]; then echo 1; else echo 0; fi', escapeshellarg($this->application->getDeploymentPath() . '/.surf/deploy.lock'));
        $this->responses = [
            $testIfDeploymentLockFileExists => true,
        ];
        $this->expectException(DeploymentLockedException::class);
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @return LockDeploymentTask|\TYPO3\Surf\Domain\Model\Task
     */
    protected function createTask()
    {
        return new LockDeploymentTask();
    }
}
