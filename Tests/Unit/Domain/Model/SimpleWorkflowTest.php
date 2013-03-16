<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Model;

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
 * Unit test for SimpleWorkflow
 */
class SimpleWorkflowTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Exception
	 */
	public function deploymentMustBeInitializedBeforeRunning() {
		$deployment = $this->buildDeployment();
		$workflow = $deployment->getWorkflow();

		$workflow->run($deployment);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Exception
	 */
	public function runFailsIfNoApplicationIsConfigured() {
		$deployment = $this->buildDeployment();
		$workflow = $deployment->getWorkflow();

		$deployment->initialize();

		try {
			$workflow->run($deployment);
		} catch(\TYPO3\Flow\Exception $exception) {
			$this->assertEquals(1334652420, $exception->getCode());
			throw $exception;
		}
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Exception
	 */
	public function runFailsIfNoNodesAreConfigured() {
		$deployment = $this->buildDeployment();
		$workflow = $deployment->getWorkflow();

		$deployment->addApplication(new \TYPO3\Surf\Domain\Model\Application('Test application'));

		$deployment->initialize();

		try {
			$workflow->run($deployment);
		} catch(\TYPO3\Flow\Exception $exception) {
			$this->assertEquals(1334652427, $exception->getCode());
			throw $exception;
		}
	}

	/**
	 * Data provider with task definitions and expected executions
	 *
	 * Tests a simple setup with one node and one application.
	 *
	 * @return array
	 */
	public function globalTaskDefinitions() {
		return array(

			array(
				'Just one global task in stage initialize',
				function($workflow, $application) {
					return function() use ($workflow, $application) {
						$workflow
							->addTask('typo3.surf:test:setup', 'initialize');
					};
				},
				array(
					array(
						'task' => 'typo3.surf:test:setup',
						'node' => 'test1.example.com',
						'application' => 'Test application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					)
				)
			),

			array(
				'Add multiple tasks with afterTask',
				function($workflow, $application) {
					return function() use ($workflow, $application) {
						$workflow
							->addTask('typo3.surf:test:setup', 'initialize')
							->afterTask('typo3.surf:test:setup', array('typo3.surf:test:secondsetup', 'typo3.surf:test:thirdsetup'))
							->afterTask('typo3.surf:test:secondsetup', 'typo3.surf:test:finalize');
					};
				},
				array(
					array(
						'task' => 'typo3.surf:test:setup',
						'node' => 'test1.example.com',
						'application' => 'Test application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:secondsetup',
						'node' => 'test1.example.com',
						'application' => 'Test application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:finalize',
						'node' => 'test1.example.com',
						'application' => 'Test application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:thirdsetup',
						'node' => 'test1.example.com',
						'application' => 'Test application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					)
				)
			),

			array(
				'Tasks in different stages',
				function($workflow, $application) {
					return function() use ($workflow, $application) {
						$workflow
							->addTask('typo3.surf:test:setup', 'initialize')
							->addTask('typo3.surf:test:checkout', 'update')
							->addTask('typo3.surf:test:symlink', 'switch');
					};
				},
				array(
					array(
						'task' => 'typo3.surf:test:setup',
						'node' => 'test1.example.com',
						'application' => 'Test application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:checkout',
						'node' => 'test1.example.com',
						'application' => 'Test application',
						'deployment' => 'Test deployment',
						'stage' => 'update',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:symlink',
						'node' => 'test1.example.com',
						'application' => 'Test application',
						'deployment' => 'Test deployment',
						'stage' => 'switch',
						'options' => array()
					)
				)
			)

		);
	}

	/**
	 * @test
	 * @dataProvider globalTaskDefinitions
	 *
	 * @param string $message
	 * @param \Closure $initializeCallback
	 * @param array $expectedExecutions
	 */
	public function globalTaskDefinitionsAreExecutedCorrectly($message, $initializeCallback, array $expectedExecutions) {
		$executedTasks = array();
		$deployment = $this->buildDeployment($executedTasks);
		$workflow = $deployment->getWorkflow();

		$application = new \TYPO3\Surf\Domain\Model\Application('Test application');
		$application->addNode(new \TYPO3\Surf\Domain\Model\Node('test1.example.com'));
		$deployment
			->addApplication($application)
			->onInitialize($initializeCallback($workflow, $application));

		$deployment->initialize();

		$workflow->run($deployment);

		$this->assertEquals($expectedExecutions, $executedTasks, $message);
	}

	/**
	 * Data provider with task definitions and expected executions
	 *
	 * A more complex setup with two applications running on three nodes.
	 *
	 * @return array
	 */
	public function applicationTaskDefinitions() {
		return array(

			array(
				'Specific tasks for applications',
				function($workflow, $applications) {
					list($flowApplication, $typo3Application) = $applications;
					return function() use ($workflow, $flowApplication, $typo3Application) {
						$workflow
							->addTask('typo3.surf:test:setup', 'initialize')
							->addTask('typo3.surf:test:doctrine:migrate', 'migrate', $flowApplication)
							->addTask('typo3.surf:test:em:updatedatabase', 'migrate', $typo3Application);
					};
				},
				array(
					array(
						'task' => 'typo3.surf:test:setup',
						'node' => 'flow-1.example.com',
						'application' => 'TYPO3 Flow Application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:setup',
						'node' => 'flow-2.example.com',
						'application' => 'TYPO3 Flow Application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:setup',
						'node' => 'neos.example.com',
						'application' => 'TYPO3 Neos Application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:doctrine:migrate',
						'node' => 'flow-1.example.com',
						'application' => 'TYPO3 Flow Application',
						'deployment' => 'Test deployment',
						'stage' => 'migrate',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:doctrine:migrate',
						'node' => 'flow-2.example.com',
						'application' => 'TYPO3 Flow Application',
						'deployment' => 'Test deployment',
						'stage' => 'migrate',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:em:updatedatabase',
						'node' => 'neos.example.com',
						'application' => 'TYPO3 Neos Application',
						'deployment' => 'Test deployment',
						'stage' => 'migrate',
						'options' => array()
					)
				)
			)

		);
	}

	/**
	 * @test
	 * @dataProvider applicationTaskDefinitions
	 * @param string $message
	 * @param \Closure $initializeCallback
	 * @param array $expectedExecutions
	 */
	public function applicationTaskDefinitionsAreExecutedCorrectly($message, $initializeCallback, array $expectedExecutions) {
		$executedTasks = array();
		$deployment = $this->buildDeployment($executedTasks);
		$workflow = $deployment->getWorkflow();

		$flowApplication = new \TYPO3\Surf\Domain\Model\Application('TYPO3 Flow Application');
		$flowApplication
			->addNode(new \TYPO3\Surf\Domain\Model\Node('flow-1.example.com'))
			->addNode(new \TYPO3\Surf\Domain\Model\Node('flow-2.example.com'));
		$neosApplication = new \TYPO3\Surf\Domain\Model\Application('TYPO3 Neos Application');
		$neosApplication
			->addNode(new \TYPO3\Surf\Domain\Model\Node('neos.example.com'));

		$deployment
			->addApplication($flowApplication)
			->addApplication($neosApplication)
			->onInitialize($initializeCallback($workflow, array($flowApplication, $neosApplication)));

		$deployment->initialize();

		$workflow->run($deployment);

		$this->assertEquals($expectedExecutions, $executedTasks, $message);
	}

	/**
	 * Build a Deployment object with Workflow for testing
	 *
	 * @param array $executedTasks Register for executed tasks
	 * @return \TYPO3\Surf\Domain\Model\Deployment A configured Deployment for testing
	 */
	protected function buildDeployment(array &$executedTasks = array()) {
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
		$mockLogger = $this->getMock('TYPO3\Flow\Log\LoggerInterface');
			// Enable log to console to debug tests
		// $mockLogger->expects($this->any())->method('log')->will($this->returnCallback(function($message) {
		// 	echo $message . chr(10);
		// }));
		$deployment->setLogger($mockLogger);

		$mockTaskManager = $this->getMock('TYPO3\Surf\Domain\Service\TaskManager');
		$mockTaskManager->expects($this->any())->method('execute')->will($this->returnCallback(function($task, \TYPO3\Surf\Domain\Model\Node $node, \TYPO3\Surf\Domain\Model\Application $application, \TYPO3\Surf\Domain\Model\Deployment $deployment, $stage, array $options = array()) use (&$executedTasks) {
			$executedTasks[] = array('task' => $task, 'node' => $node->getName(), 'application' => $application->getName(), 'deployment' => $deployment->getName(), 'stage' => $stage, 'options' => $options);
		}));

		$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();
		\TYPO3\Flow\Reflection\ObjectAccess::setProperty($workflow, 'taskManager', $mockTaskManager, TRUE);

		$deployment->setWorkflow($workflow);

		return $deployment;
	}

	/**
	 * @test
	 */
	public function tasksAreExecutedInTheRightOrder() {
		$executedTasks = array();
		$deployment = $this->buildDeployment($executedTasks);
		$workflow = $deployment->getWorkflow();

		$flowApplication = new \TYPO3\Surf\Domain\Model\Application('TYPO3 Flow Application');
		$flowApplication->addNode(new \TYPO3\Surf\Domain\Model\Node('flow-1.example.com'));

		$deployment->addApplication($flowApplication);

		$deployment->initialize();

		$workflow->addTask('task1:package', 'package');

		$workflow->addTask('task1:initialize', 'initialize');
		$workflow->addTask('task3:initialize', 'initialize');
		$workflow->afterTask('task1:initialize', 'task2:initialize');

		$workflow->beforeStage('initialize', 'before1:initialize');
		$workflow->beforeStage('initialize', 'before3:initialize');
		$workflow->afterTask('before1:initialize', 'before2:initialize');

		$workflow->afterStage('initialize', 'after1:initialize');
		$workflow->afterStage('initialize', 'after3:initialize');
		$workflow->afterTask('after1:initialize', 'after2:initialize');

		$workflow->run($deployment);

		$expected = array(
			array (
				'task' => 'before1:initialize',
				'node' => 'flow-1.example.com',
				'application' => 'TYPO3 Flow Application',
				'deployment' => 'Test deployment',
				'stage' => 'initialize',
				'options' => array()
			),
			array (
				'task' => 'before2:initialize',
				'node' => 'flow-1.example.com',
				'application' => 'TYPO3 Flow Application',
				'deployment' => 'Test deployment',
				'stage' => 'initialize',
				'options' => array()
			),
			array (
				'task' => 'before3:initialize',
				'node' => 'flow-1.example.com',
				'application' => 'TYPO3 Flow Application',
				'deployment' => 'Test deployment',
				'stage' => 'initialize',
				'options' => array()
			),
			array (
				'task' => 'task1:initialize',
				'node' => 'flow-1.example.com',
				'application' => 'TYPO3 Flow Application',
				'deployment' => 'Test deployment',
				'stage' => 'initialize',
				'options' => array()
			),
			array (
				'task' => 'task2:initialize',
				'node' => 'flow-1.example.com',
				'application' => 'TYPO3 Flow Application',
				'deployment' => 'Test deployment',
				'stage' => 'initialize',
				'options' => array()
			),
			array (
				'task' => 'task3:initialize',
				'node' => 'flow-1.example.com',
				'application' => 'TYPO3 Flow Application',
				'deployment' => 'Test deployment',
				'stage' => 'initialize',
				'options' => array()
			),
			array (
				'task' => 'after1:initialize',
				'node' => 'flow-1.example.com',
				'application' => 'TYPO3 Flow Application',
				'deployment' => 'Test deployment',
				'stage' => 'initialize',
				'options' => array()
			),
			array (
				'task' => 'after2:initialize',
				'node' => 'flow-1.example.com',
				'application' => 'TYPO3 Flow Application',
				'deployment' => 'Test deployment',
				'stage' => 'initialize',
				'options' => array()
			),
			array (
				'task' => 'after3:initialize',
				'node' => 'flow-1.example.com',
				'application' => 'TYPO3 Flow Application',
				'deployment' => 'Test deployment',
				'stage' => 'initialize',
				'options' => array()
			),
			array (
				'task' => 'task1:package',
				'node' => 'flow-1.example.com',
				'application' => 'TYPO3 Flow Application',
				'deployment' => 'Test deployment',
				'stage' => 'package',
				'options' => array()
			)
		);

		$this->assertEquals($expected, $executedTasks);
	}

}
?>