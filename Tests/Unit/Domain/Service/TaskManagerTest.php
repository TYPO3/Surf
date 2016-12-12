<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

/**
 * Unit test for the TaskManager
 */
class TaskManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \TYPO3\Surf\Domain\Model\Task|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $task;

    /**
     * @var \TYPO3\Surf\Domain\Model\Node
     */
    protected $node;

    /**
     * @var \TYPO3\Surf\Domain\Model\Application
     */
    protected $application;

    /**
     * @var \TYPO3\Surf\Domain\Model\Deployment
     */
    protected $deployment;

    /**
     * @var \TYPO3\Surf\Domain\Service\TaskManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taskManager;

    protected function setUp()
    {
        $this->node = new \TYPO3\Surf\Domain\Model\Node('Test node');
        $this->application = new \TYPO3\Surf\Domain\Model\Application('Test application');
        $this->deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->deployment->setLogger($logger);
        $this->task = $this->getMock(\TYPO3\Surf\Domain\Model\Task::class);

        $this->taskManager = $this->getMock(\TYPO3\Surf\Domain\Service\TaskManager::class, array('createTaskInstance'));
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
        $globalOptions = array(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Foo'
        );
        $this->deployment->setOptions($globalOptions);

        $this->task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->arrayHasKey('taskOption')
        );

        $localOptions = array();
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function executePassesNodeOptionsToTask()
    {
        $nodeOptions = array(
            'ssh[username]' => 'jdoe'
        );
        $this->node->setOptions($nodeOptions);

        $this->task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(), $this->anything(), $this->anything(),
            $this->arrayHasKey('ssh[username]')
        );

        $localOptions = array();
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function executePassesApplicationOptionsToTask()
    {
        $applicationOptions = array(
            'repositoryUrl' => 'ssh://review.typo3.org/foo'
        );
        $this->application->setOptions($applicationOptions);

        $this->task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(), $this->anything(), $this->anything(),
            $this->arrayHasKey('repositoryUrl')
        );

        $localOptions = array();
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function nodeOptionsOverrideDeploymentOptions()
    {
        $globalOptions = array(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Deployment'
        );
        $this->deployment->setOptions($globalOptions);
        $nodeOptions = array(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Node'
        );
        $this->node->setOptions($nodeOptions);

        $this->task
            ->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturnCallback(function ($_, $__, $___, $options) {
                if ($options['taskOption'] !== 'Node') {
                    throw new \RuntimeException('Node options do not override deployment options!');
                }
            });

        $localOptions = array();
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function applicationOptionsOverrideNodeOptions()
    {
        $nodeOptions = array(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Node'
        );
        $this->node->setOptions($nodeOptions);
        $applicationOptions = array(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Application'
        );
        $this->application->setOptions($applicationOptions);

        $this->task
            ->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturnCallback(function ($_, $__, $___, $options) {
                if ($options['taskOption'] !== 'Application') {
                    throw new \RuntimeException('Node options do not override deployment options!');
                }
            });

        $localOptions = array();
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function applicationOptionsOverrideDeploymentOptions()
    {
        $globalOptions = array(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Deployment'
        );
        $this->deployment->setOptions($globalOptions);
        $applicationOptions = array(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Application'
        );
        $this->application->setOptions($applicationOptions);

        $this->task
            ->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturnCallback(function ($_, $__, $___, $options) {
                if ($options['taskOption'] !== 'Application') {
                    throw new \RuntimeException('Node options do not override deployment options!');
                }
            });

        $localOptions = array();
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function executeDoesNotPassPrefixedTaskOptionsOfBaseTaskToDefinedTask()
    {
        $globalOptions = array(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Foo'
        );
        $this->deployment->setOptions($globalOptions);

        $this->task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->logicalNot($this->arrayHasKey('taskOption'))
        );

        $localOptions = array();
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions, 'MyVendor\\MyPackage\\DefinedTask\\TaskGroup\\MyTask');
    }

    /**
     * @test
     */
    public function executePassePrefixedDefinedTaskOptionsToDefinedTask()
    {
        $globalOptions = array(
            'MyVendor\\MyPackage\\DefinedTask\\TaskGroup\\MyTask[taskOption]' => 'Foo'
        );
        $this->deployment->setOptions($globalOptions);

        $this->task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->arrayHasKey('taskOption')
        );

        $localOptions = array();
        $this->taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $this->node, $this->application, $this->deployment, 'test', $localOptions, 'MyVendor\\MyPackage\\DefinedTask\\TaskGroup\\MyTask');
    }
}
