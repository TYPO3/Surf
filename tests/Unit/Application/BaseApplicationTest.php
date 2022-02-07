<?php

namespace TYPO3\Surf\Tests\Unit\Application;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Task\GitCheckoutTask;
use TYPO3\Surf\Task\Transfer\ScpTask;
use TYPO3\Surf\Tests\Unit\FluidPromise;

class BaseApplicationTest extends TestCase
{
    private BaseApplication $subject;

    protected function setUp(): void
    {
        $this->subject = new BaseApplication('Base Application');
    }

    /**
     * @test
     */
    public function addDirectories(): void
    {
        $directories = ['dir1', 'dir2'];
        $this->subject->addDirectories($directories);

        self::assertContains('dir1', $this->subject->getDirectories());
        self::assertContains('dir2', $this->subject->getDirectories());
    }

    /**
     * @test
     */
    public function setSymlinks(): void
    {
        $symlinks = ['toPath' => 'symlink1'];
        $this->subject->setSymlinks($symlinks);

        self::assertSame($this->subject->getSymlinks(), $symlinks);
    }

    /**
     * @test
     */
    public function setDirectories(): void
    {
        $directories = ['dir1', 'dir2'];
        $this->subject->setDirectories($directories);

        self::assertSame($this->subject->getDirectories(), $directories);
    }

    /**
     * @test
     */
    public function registerTasksForGitTransferMethod(): void
    {
        $workflow = $this->createWorkflow();

        $workflow->addTask(GitCheckoutTask::class, 'transfer', $this->subject)->shouldBeCalledOnce();
        $deployment = $this->createDeployment();

        $this->subject->setOption('transferMethod', 'git');
        $this->subject->registerTasks($workflow->reveal(), $deployment->reveal());
    }

    /**
     * @test
     */
    public function registerTasksForScpTransferMethod(): void
    {
        $workflow = $this->createWorkflow();

        $workflow->addTask(ScpTask::class, 'transfer', $this->subject)->shouldBeCalledOnce();
        $deployment = $this->createDeployment();

        $this->subject->setOption('transferMethod', 'scp');
        $this->subject->registerTasks($workflow->reveal(), $deployment->reveal());
    }

    /**
     * @test
     */
    public function addSymlink(): void
    {
        $this->subject->addSymlink('toPath', 'fromPath');

        self::assertContains('fromPath', $this->subject->getSymlinks());
    }

    /**
     * @test
     */
    public function addSymlinks(): void
    {
        $symlinks = ['toPath' => 'fromPath'];
        $this->subject->addSymlinks($symlinks);

        self::assertContains('fromPath', $this->subject->getSymlinks());
    }

    /**
     * @test
     */
    public function addDirectory(): void
    {
        $this->subject->addDirectory('toPath');
        self::assertContains('toPath', $this->subject->getDirectories());
    }

    /**
     * @return ObjectProphecy|Workflow
     */
    private function createWorkflow(): ObjectProphecy
    {
        $workflow = $this->prophesize(Workflow::class);
        $workflow->addTask(Argument::any(), Argument::any(), $this->subject)->will(new FluidPromise());
        $workflow->afterTask(Argument::any(), Argument::any(), $this->subject)->will(new FluidPromise());
        $workflow->afterStage(Argument::any(), Argument::any(), $this->subject)->will(new FluidPromise());
        $workflow->defineTask(Argument::any(), Argument::any(), Argument::type('array'))->will(new FluidPromise());

        return $workflow;
    }

    /**
     * @return Deployment|ObjectProphecy
     */
    private function createDeployment()
    {
        $deployment = $this->prophesize(Deployment::class);
        $deployment->getForceRun()->willReturn(false);

        return $deployment;
    }
}
