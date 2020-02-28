<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;
use RuntimeException;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\TaskManager;

/**
 * Unit test for the TaskManager
 */
class TaskManagerTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Task
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
     * @var PHPUnit_Framework_MockObject_MockObject|TaskManager
     */
    protected $taskManager;

    protected function setUp()
    {
        $this->node = new Node('Test node');
        $this->application = new Application('Test application');
        $this->deployment = new Deployment('Test deployment');
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $this->deployment->setLogger($logger);
        $this->task = $this->createMock(Task::class);

        $this->taskManager = $this->createPartialMock(TaskManager::class, ['createTaskInstance']);
        $this->taskManager
            ->expects($this->any())
            ->method('createTaskInstance')
            ->will($this->returnValue($this->task));
    }

    /**
     * @test
     */
    public function executePassesPrefixedTaskOptionsToTask()
    {
        $globalOptions = [
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Foo'
        ];
        $this->deployment->setOptions($globalOptions);

        $this->task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->arrayHasKey('taskOption')
        );

        $localOptions = [];
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function executePassesNodeOptionsToTask()
    {
        $nodeOptions = [
            'ssh[username]' => 'jdoe'
        ];
        $this->node->setOptions($nodeOptions);

        $this->task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->arrayHasKey('ssh[username]')
        );

        $localOptions = [];
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function executePassesApplicationOptionsToTask()
    {
        $applicationOptions = [
            'repositoryUrl' => 'ssh://review.typo3.org/foo'
        ];
        $this->application->setOptions($applicationOptions);

        $this->task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->arrayHasKey('repositoryUrl')
        );

        $localOptions = [];
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function nodeOptionsOverrideDeploymentOptions()
    {
        $globalOptions = [
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Deployment'
        ];
        $this->deployment->setOptions($globalOptions);
        $nodeOptions = [
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Node'
        ];
        $this->node->setOptions($nodeOptions);

        $this->task
            ->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturnCallback(function ($_, $__, $___, $options) {
                if ($options['taskOption'] !== 'Node') {
                    throw new RuntimeException('Node options do not override deployment options!');
                }
            });

        $localOptions = [];
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function applicationOptionsOverrideNodeOptions()
    {
        $nodeOptions = [
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Node'
        ];
        $this->node->setOptions($nodeOptions);
        $applicationOptions = [
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Application'
        ];
        $this->application->setOptions($applicationOptions);

        $this->task
            ->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturnCallback(function ($_, $__, $___, $options) {
                if ($options['taskOption'] !== 'Application') {
                    throw new \RuntimeException('Node options do not override deployment options!');
                }
            });

        $localOptions = [];
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function applicationOptionsOverrideDeploymentOptions()
    {
        $globalOptions = [
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Deployment'
        ];
        $this->deployment->setOptions($globalOptions);
        $applicationOptions = [
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Application'
        ];
        $this->application->setOptions($applicationOptions);

        $this->task
            ->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturnCallback(function ($_, $__, $___, $options) {
                if ($options['taskOption'] !== 'Application') {
                    throw new \RuntimeException('Node options do not override deployment options!');
                }
            });

        $localOptions = [];
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function executeDoesNotPassPrefixedTaskOptionsOfBaseTaskToDefinedTask()
    {
        $globalOptions = [
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Foo'
        ];
        $this->deployment->setOptions($globalOptions);

        $this->task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->logicalNot($this->arrayHasKey('taskOption'))
        );

        $localOptions = [];
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions, 'MyVendor\\MyPackage\\DefinedTask\\TaskGroup\\MyTask');
    }

    /**
     * @test
     */
    public function executePassePrefixedDefinedTaskOptionsToDefinedTask()
    {
        $globalOptions = [
            'MyVendor\\MyPackage\\DefinedTask\\TaskGroup\\MyTask[taskOption]' => 'Foo'
        ];
        $this->deployment->setOptions($globalOptions);

        $this->task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->arrayHasKey('taskOption')
        );

        $localOptions = [];
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions, 'MyVendor\\MyPackage\\DefinedTask\\TaskGroup\\MyTask');
    }
}
