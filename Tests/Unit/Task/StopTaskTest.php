<?php
namespace TYPO3\Surf\Tests\Unit\Task;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

/**
 * Unit test for the StopTask
 */
class StopTaskTest extends BaseTaskTest {

	/**
	 * @test
	 * @expectedException \TYPO3\Surf\Exception\StopWorkflowException
	 */
	public function executeThrowsStopWorkflowException() {
		$this->task->execute($this->node, $this->application, $this->deployment);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Surf\Exception\StopWorkflowException
	 */
	public function simulateThrowsStopWorkflowException() {
		$this->task->simulate($this->node, $this->application, $this->deployment);
	}

	/**
	 * @return \TYPO3\Surf\Domain\Model\Task
	 */
	protected function createTask() {
		return new \TYPO3\Surf\Task\StopTask();
	}

}
?>