<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\TaskFactory;
use TYPO3\Surf\Domain\Service\TaskManager;

class TaskManagerTest extends TestCase
{
    use ProphecyTrait;
    /**
     * @var ObjectProphecy|Task
     */
    protected $task;

    /**
     * @var Node
     */
    protected $node;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Deployment
     */
    protected $deployment;

    /**
     * @var TaskManager
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->node = new Node('Test node');
        $this->application = new Application('Test application');
        $this->deployment = new Deployment('Test deployment');

        $logger = $this->prophesize(LoggerInterface::class);
        $this->deployment->setLogger($logger->reveal());
        $this->task = $this->prophesize(Task::class);

        $taskFactory = $this->prophesize(TaskFactory::class);
        $taskFactory->createTaskInstance(Argument::any())->willReturn($this->task);

        $this->subject = new TaskManager($taskFactory->reveal());
        $this->subject->setLogger($logger->reveal());
    }

    /**
     * @test
     */
    public function executePassesPrefixedTaskOptionsToTask(): void
    {
        $globalOptions = [
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Foo'
        ];
        $this->deployment->setOptions($globalOptions);

        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            Argument::withEntry('taskOption', 'Foo')
        )->shouldBeCalledOnce();

        $localOptions = [];
        $this->subject->execute(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask',
            $this->node,
            $this->application,
            $this->deployment,
            'test',
            $localOptions
        );
    }

    /**
     * @test
     */
    public function executePassesNodeOptionsToTask(): void
    {
        $nodeOptions = [
            'ssh[username]' => 'jdoe'
        ];
        $this->node->setOptions($nodeOptions);

        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            Argument::withEntry('ssh[username]', 'jdoe')
        )->shouldBeCalledOnce();

        $localOptions = [];
        $this->subject->execute(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask',
            $this->node,
            $this->application,
            $this->deployment,
            'test',
            $localOptions
        );
    }

    /**
     * @test
     */
    public function executePassesApplicationOptionsToTask(): void
    {
        $applicationOptions = [
            'repositoryUrl' => 'ssh://review.typo3.org/foo'
        ];
        $this->application->setOptions($applicationOptions);

        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            Argument::withEntry('repositoryUrl', 'ssh://review.typo3.org/foo')
        )->shouldBeCalledOnce();

        $localOptions = [];
        $this->subject->execute(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask',
            $this->node,
            $this->application,
            $this->deployment,
            'test',
            $localOptions
        );
    }

    /**
     * @test
     */
    public function executeDoesNotPassPrefixedTaskOptionsOfBaseTaskToDefinedTask(): void
    {
        $globalOptions = [
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Foo'
        ];
        $this->deployment->setOptions($globalOptions);

        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            Argument::not(Argument::withKey('taskOption'))
        )->shouldBeCalledOnce();

        $localOptions = [];
        $this->subject->execute(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask',
            $this->node,
            $this->application,
            $this->deployment,
            'test',
            $localOptions,
            'MyVendor\\MyPackage\\DefinedTask\\TaskGroup\\MyTask'
        );
    }

    /**
     * @test
     */
    public function executePassePrefixedDefinedTaskOptionsToDefinedTask(): void
    {
        $globalOptions = [
            'MyVendor\\MyPackage\\DefinedTask\\TaskGroup\\MyTask[taskOption]' => 'Foo'
        ];
        $this->deployment->setOptions($globalOptions);

        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            Argument::withEntry('taskOption', 'Foo')
        )->shouldBeCalledOnce();

        $localOptions = [];
        $this->subject->execute(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask',
            $this->node,
            $this->application,
            $this->deployment,
            'test',
            $localOptions,
            'MyVendor\\MyPackage\\DefinedTask\\TaskGroup\\MyTask'
        );
    }

    /**
     * @test
     */
    public function rollBack(): void
    {
        $this->task->rollback($this->node, $this->application, $this->deployment, Argument::any())->shouldBeCalledOnce();
        $this->task->execute($this->node, $this->application, $this->deployment, Argument::any())->shouldBeCalledOnce();

        $this->subject->execute(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask',
            $this->node,
            $this->application,
            $this->deployment,
            'test'
        );
        $this->subject->rollback();
    }

    /**
     * @test
     */
    public function rollBackForTaskIsNotCalledInDryRun(): void
    {
        $this->deployment->setDryRun(true);

        $this->task->rollback(
            $this->node,
            $this->application,
            $this->deployment,
            Argument::any()
        )->shouldNotHaveBeenCalled();

        $this->task->simulate(
            $this->node,
            $this->application,
            $this->deployment,
            Argument::any()
        )->shouldBeCalledOnce();

        $this->subject->execute(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask',
            $this->node,
            $this->application,
            $this->deployment,
            'test'
        );
        $this->subject->rollback();
    }
}
