<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Unit test for the TaskManager
 */
class TaskManagerTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function executePassesPrefixedTaskOptionsToTask() {
		$node = new \TYPO3\Surf\Domain\Model\Node('Test node');
		$application = new \TYPO3\Surf\Domain\Model\Application('Test application');
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
		$logger = $this->getMock('TYPO3\Flow\Log\LoggerInterface');
		$deployment->setLogger($logger);

		$task = $this->getMock('TYPO3\Surf\Domain\Model\Task');
		$taskManager = $this->getAccessibleMock('TYPO3\Surf\Domain\Service\TaskManager', array('createTaskInstance'));
		$taskManager->expects($this->any())->method('createTaskInstance')->with('myvendor.mypackage:taskgroup:mytask')->will($this->returnValue($task));

		$globalOptions = array(
			'myvendor.mypackage:taskgroup:mytask[taskOption]' => 'Foo'
		);
		$deployment->setOptions($globalOptions);

		$task->expects($this->atLeastOnce())->method('execute')->with(
			$this->anything(), $this->anything(), $this->anything(),
			$this->logicalOr(
				$this->arrayHasKey('taskOption'),
				$this->arrayHasKey('myvendor.mypackage:taskgroup:mytask[taskOption]')
			)
		);

		$localOptions = array();
		$taskManager->execute('myvendor.mypackage:taskgroup:mytask', $node, $application, $deployment, 'test', $localOptions);
	}

	/**
	 * @test
	 */
	public function executePassesNodeOptionsToTask() {
		$node = new \TYPO3\Surf\Domain\Model\Node('Test node');
		$application = new \TYPO3\Surf\Domain\Model\Application('Test application');
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
		$logger = $this->getMock('TYPO3\Flow\Log\LoggerInterface');
		$deployment->setLogger($logger);

		$task = $this->getMock('TYPO3\Surf\Domain\Model\Task');
		$taskManager = $this->getAccessibleMock('TYPO3\Surf\Domain\Service\TaskManager', array('createTaskInstance'));
		$taskManager->expects($this->any())->method('createTaskInstance')->with('myvendor.mypackage:taskgroup:mytask')->will($this->returnValue($task));

		$nodeOptions = array(
			'ssh[username]' => 'jdoe'
		);
		$node->setOptions($nodeOptions);

		$task->expects($this->atLeastOnce())->method('execute')->with(
			$this->anything(), $this->anything(), $this->anything(),
			$this->logicalOr(
				$this->arrayHasKey('ssh[username]')
			)
		);

		$localOptions = array();
		$taskManager->execute('myvendor.mypackage:taskgroup:mytask', $node, $application, $deployment, 'test', $localOptions);
	}

	/**
	 * @test
	 */
	public function executePassesApplicationOptionsToTask() {
		$node = new \TYPO3\Surf\Domain\Model\Node('Test node');
		$application = new \TYPO3\Surf\Domain\Model\Application('Test application');
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
		$logger = $this->getMock('TYPO3\Flow\Log\LoggerInterface');
		$deployment->setLogger($logger);

		$task = $this->getMock('TYPO3\Surf\Domain\Model\Task');
		$taskManager = $this->getAccessibleMock('TYPO3\Surf\Domain\Service\TaskManager', array('createTaskInstance'));
		$taskManager->expects($this->any())->method('createTaskInstance')->with('myvendor.mypackage:taskgroup:mytask')->will($this->returnValue($task));

		$applicationOptions = array(
			'repositoryUrl' => 'ssh://review.typo3.org/foo'
		);
		$application->setOptions($applicationOptions);

		$task->expects($this->atLeastOnce())->method('execute')->with(
			$this->anything(), $this->anything(), $this->anything(),
			$this->logicalOr(
				$this->arrayHasKey('repositoryUrl')
			)
		);

		$localOptions = array();
		$taskManager->execute('myvendor.mypackage:taskgroup:mytask', $node, $application, $deployment, 'test', $localOptions);
	}

}
?>