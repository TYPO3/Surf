<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the SymlinkConfigurationTask
 */
class SymlinkConfigurationTaskTest extends BaseTaskTest {

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
	public function executeWithFlowApplicationRespectsContext() {
		$this->application->setContext('Development');
		$this->task->execute($this->node, $this->application, $this->deployment, array());

		$this->assertCommandExecuted('if [ -d Development ]; then rm -Rf Development; fi');
		$this->assertCommandExecuted('mkdir -p ../../../shared/Configuration/Development');
		$this->assertCommandExecuted('ln -snf ../../../shared/Configuration/Development Development');
	}

	/**
	 * @test
	 */
	public function executeWithFlowApplicationRespectsSubContext() {
		$this->application->setContext('Production/Foo');
		$this->task->execute($this->node, $this->application, $this->deployment, array());

		$this->assertCommandExecuted('if [ -d Production/Foo ]; then rm -Rf Production/Foo; fi');
		$this->assertCommandExecuted('mkdir -p ../../../shared/Configuration/Production/Foo');
		$this->assertCommandExecuted('mkdir -p Production');
		$this->assertCommandExecuted('ln -snf ../../../../shared/Configuration/Production/Foo Production/Foo');

	}

	/**
	 * @return \TYPO3\Surf\Domain\Model\Task
	 */
	protected function createTask() {
		return new \TYPO3\Surf\Task\TYPO3\Flow\SymlinkConfigurationTask();
	}

}
?>