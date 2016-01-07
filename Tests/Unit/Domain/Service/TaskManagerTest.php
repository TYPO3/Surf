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
     * @test
     */
    public function executePassesPrefixedTaskOptionsToTask()
    {
        $node = new \TYPO3\Surf\Domain\Model\Node('Test node');
        $application = new \TYPO3\Surf\Domain\Model\Application('Test application');
        $deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $deployment->setLogger($logger);
        $task = $this->getMock('TYPO3\Surf\Domain\Model\Task');
        /** @var \TYPO3\Surf\Domain\Service\TaskManager|\PHPUnit_Framework_MockObject_MockObject $taskManager */
        $taskManager = $this->getMock('TYPO3\Surf\Domain\Service\TaskManager', array('createTaskInstance'));
        $taskManager->expects($this->any())->method('createTaskInstance')->with('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask')->will($this->returnValue($task));

        $globalOptions = array(
            'MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask[taskOption]' => 'Foo'
        );
        $deployment->setOptions($globalOptions);

        $task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(), $this->anything(), $this->anything(),
            $this->arrayHasKey('taskOption')
        );

        $localOptions = array();
        $taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $node, $application, $deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function executePassesNodeOptionsToTask()
    {
        $node = new \TYPO3\Surf\Domain\Model\Node('Test node');
        $application = new \TYPO3\Surf\Domain\Model\Application('Test application');
        $deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $deployment->setLogger($logger);
        $task = $this->getMock('TYPO3\Surf\Domain\Model\Task');
        /** @var \TYPO3\Surf\Domain\Service\TaskManager|\PHPUnit_Framework_MockObject_MockObject $taskManager */
        $taskManager = $this->getMock('TYPO3\Surf\Domain\Service\TaskManager', array('createTaskInstance'));
        $taskManager->expects($this->any())->method('createTaskInstance')->with('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask')->will($this->returnValue($task));

        $nodeOptions = array(
            'ssh[username]' => 'jdoe'
        );
        $node->setOptions($nodeOptions);

        $task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(), $this->anything(), $this->anything(),
            $this->arrayHasKey('ssh[username]')
        );

        $localOptions = array();
        $taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $node, $application, $deployment, 'test', $localOptions);
    }

    /**
     * @test
     */
    public function executePassesApplicationOptionsToTask()
    {
        $node = new \TYPO3\Surf\Domain\Model\Node('Test node');
        $application = new \TYPO3\Surf\Domain\Model\Application('Test application');
        $deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $deployment->setLogger($logger);
        $task = $this->getMock('TYPO3\Surf\Domain\Model\Task');
        /** @var \TYPO3\Surf\Domain\Service\TaskManager|\PHPUnit_Framework_MockObject_MockObject $taskManager */
        $taskManager = $this->getMock('TYPO3\Surf\Domain\Service\TaskManager', array('createTaskInstance'));
        $taskManager->expects($this->any())->method('createTaskInstance')->with('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask')->will($this->returnValue($task));

        $applicationOptions = array(
            'repositoryUrl' => 'ssh://review.typo3.org/foo'
        );
        $application->setOptions($applicationOptions);

        $task->expects($this->atLeastOnce())->method('execute')->with(
            $this->anything(), $this->anything(), $this->anything(),
            $this->arrayHasKey('repositoryUrl')
        );

        $localOptions = array();
        $taskManager->execute('MyVendor\\MyPackage\\Task\\TaskGroup\\MyTask', $node, $application, $deployment, 'test', $localOptions);
    }
}
