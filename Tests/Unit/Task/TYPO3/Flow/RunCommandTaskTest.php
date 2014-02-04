<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the RunCommandTask
 */
class RunCommandTaskTest extends BaseTaskTest {

	/**
	 * Set up test dependencies
	 */
	public function setUp() {
		parent::setUp();

		$this->application = new \TYPO3\Surf\Application\TYPO3\Flow('TestApplication');
		$this->application->setDeploymentPath('/home/jdoe/app');
	}

	/**
	 * @test
	 */
	public function executeWithSingleStringArgumentsEscapesFullArgument() {
		$options = array(
			'command' => 'example:command',
			'arguments' => 'Some longer argument needing "escaping"',
		);
		$this->task->execute($this->node, $this->application, $this->deployment, $options);

		$this->assertCommandExecuted('./flow example:command \'Some longer argument needing "escaping"\'');
	}

	/**
	 * @test
	 */
	public function executeWithArrayArgumentsEscapesIndividualArguments() {
		$options = array(
			'command' => 'site:prune',
			'arguments' => array('--confirmation', 'TRUE'),
		);
		$this->task->execute($this->node, $this->application, $this->deployment, $options);

		$this->assertCommandExecuted('./flow site:prune \'--confirmation\' \'TRUE\'');
	}

	/**
	 * @return \TYPO3\Surf\Domain\Model\Task
	 */
	protected function createTask() {
		return new \TYPO3\Surf\Task\TYPO3\Flow\RunCommandTask();
	}

}
?>