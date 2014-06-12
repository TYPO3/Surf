<?php
namespace TYPO3\Surf\Tests\Unit\Task\Git;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the TagTask
 */
class TagTaskTest extends BaseTaskTest {

	/**
	 * Set up test dependencies
	 */
	public function setUp() {
		parent::setUp();

		$this->application->setDeploymentPath('/home/jdoe/app');
	}

	/**
	 * @test
	 */
	public function executeWithRequiredOptionsAndPushTagCreatesAndPushesTag() {
		$options = array(
			'tagName' => 'release-{releaseIdentifier}',
			'description' => 'Release {releaseIdentifier} - by Surf.',
			'pushTag' => TRUE
		);
		$this->task->execute($this->node, $this->application, $this->deployment, $options);

		$this->assertCommandExecuted('git tag -f -a -m \'Release ' . $this->deployment->getReleaseIdentifier() . ' - by Surf.\' \'release-' . $this->deployment->getReleaseIdentifier() . '\'');
		$this->assertCommandExecuted('git push \'origin\' \'release-' . $this->deployment->getReleaseIdentifier() . '\'');
	}

	/**
	 * @test
	 */
	public function executeWithRequiredOptionsAndRecurseIntoSubmodulesCreatesTagOnRootAndSubmodules() {
		$options = array(
			'tagName' => 'release-{releaseIdentifier}',
			'description' => 'Release {releaseIdentifier} - by Surf.',
			'recurseIntoSubmodules' => TRUE
		);
		$this->task->execute($this->node, $this->application, $this->deployment, $options);

		$this->assertCommandExecuted('git tag -f -a -m \'Release ' . $this->deployment->getReleaseIdentifier() . ' - by Surf.\' \'release-' . $this->deployment->getReleaseIdentifier() . '\'');
		$this->assertCommandExecuted("git submodule foreach 'git tag -f -a -m '\\''Release {$this->deployment->getReleaseIdentifier()} - by Surf.'\\'' '\\''release-{$this->deployment->getReleaseIdentifier()}'\\'''");
	}

	/**
	 * @return \TYPO3\Surf\Domain\Model\Task
	 */
	protected function createTask() {
		return new \TYPO3\Surf\Task\Git\TagTask();
	}

}
?>