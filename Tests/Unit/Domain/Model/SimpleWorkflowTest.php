<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
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
class SimpleWorkflowTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\Exception
	 */
	public function deploymentMustBeInitializedBeforeRunning() {
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
		$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();

		$workflow->run($deployment);
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

		$this->assertEquals($expectedExecutions, $executedTasks);
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
					list($flow3Application, $typo3Application) = $applications;
					return function() use ($workflow, $flow3Application, $typo3Application) {
						$workflow
							->addTask('typo3.surf:test:setup', 'initialize')
							->addTask('typo3.surf:test:doctrine:migrate', 'migrate', $flow3Application)
							->addTask('typo3.surf:test:em:updatedatabase', 'migrate', $typo3Application);
					};
				},
				array(
					array(
						'task' => 'typo3.surf:test:setup',
						'node' => 'flow3-1.example.com',
						'application' => 'FLOW3 Application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:setup',
						'node' => 'flow3-2.example.com',
						'application' => 'FLOW3 Application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:setup',
						'node' => 'typo3.example.com',
						'application' => 'TYPO3 Application',
						'deployment' => 'Test deployment',
						'stage' => 'initialize',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:doctrine:migrate',
						'node' => 'flow3-1.example.com',
						'application' => 'FLOW3 Application',
						'deployment' => 'Test deployment',
						'stage' => 'migrate',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:doctrine:migrate',
						'node' => 'flow3-2.example.com',
						'application' => 'FLOW3 Application',
						'deployment' => 'Test deployment',
						'stage' => 'migrate',
						'options' => array()
					),
					array(
						'task' => 'typo3.surf:test:em:updatedatabase',
						'node' => 'typo3.example.com',
						'application' => 'TYPO3 Application',
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

		$flow3Application = new \TYPO3\Surf\Domain\Model\Application('FLOW3 Application');
		$flow3Application
			->addNode(new \TYPO3\Surf\Domain\Model\Node('flow3-1.example.com'))
			->addNode(new \TYPO3\Surf\Domain\Model\Node('flow3-2.example.com'));
		$typo3Application = new \TYPO3\Surf\Domain\Model\Application('TYPO3 Application');
		$typo3Application
			->addNode(new \TYPO3\Surf\Domain\Model\Node('typo3.example.com'));

		$deployment
			->addApplication($flow3Application)
			->addApplication($typo3Application)
			->onInitialize($initializeCallback($workflow, array($flow3Application, $typo3Application)));

		$deployment->initialize();

		$workflow->run($deployment);

		$this->assertEquals($expectedExecutions, $executedTasks);
	}

	/**
	 * Build a Deployment object with Workflow for testing
	 *
	 * @param array $executedTasks Register for executed tasks
	 * @return \TYPO3\Surf\Domain\Model\Deployment A configured Deployment for testing
	 */
	protected function buildDeployment(array &$executedTasks) {
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
		$mockLogger = $this->getMock('TYPO3\FLOW3\Log\LoggerInterface');
		$mockLogger->expects($this->any())->method('log')->will($this->returnCallback(function($message) {
			echo $message . chr(10);
		}));
		$deployment->setLogger($mockLogger);

		$mockTaskManager = $this->getMock('TYPO3\Surf\Domain\Service\TaskManager');
		$mockTaskManager->expects($this->any())->method('execute')->will($this->returnCallback(function($task, \TYPO3\Surf\Domain\Model\Node $node, \TYPO3\Surf\Domain\Model\Application $application, \TYPO3\Surf\Domain\Model\Deployment $deployment, $stage, array $options = array()) use (&$executedTasks) {
			$executedTasks[] = array('task' => $task, 'node' => $node->getName(), 'application' => $application->getName(), 'deployment' => $deployment->getName(), 'stage' => $stage, 'options' => $options);
		}));

		$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();
		\TYPO3\FLOW3\Reflection\ObjectAccess::setProperty($workflow, 'taskManager', $mockTaskManager, TRUE);

		$deployment->setWorkflow($workflow);

		return $deployment;
	}

}
?>