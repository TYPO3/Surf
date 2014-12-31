<?php
namespace TYPO3\Surf\Tests\Unit\Task\Transfer;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the RsyncTask
 */
class RsyncTaskTest extends BaseTaskTest {

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
	public function executeWithUsernameAndDefaultOptionsCreatesDirectoryAndTransfersAndCopiesFiles() {
		$this->node->setOption('hostname', 'myserver.local');
		$this->node->setOption('username', 'jdoe');

		$this->task->execute($this->node, $this->application, $this->deployment, array());

		$this->assertCommandExecuted('mkdir -p /home/jdoe/app/cache/transfer');
		$this->assertCommandExecuted('/rsync -q --compress --rsh="ssh "  --recursive --times --perms --links --delete --delete-excluded --exclude \'.git\' \'.*\/Data\/Surf\/TestDeployment\/TestApplication\/.\' \'jdoe@myserver.local:\/home\/jdoe\/app\/cache\/transfer\'/');
		$this->assertCommandExecuted('/cp -RPp \/home\/jdoe\/app\/cache\/transfer\/. \/home\/jdoe\/app\/releases\/[0-9]+/');
	}

	/**
	 * @test
	 */
	public function executeWithoutUsernameDoesNotAppendUsernameToRsyncTarget() {
		$this->node->setOption('hostname', 'myserver.local');

		$this->task->execute($this->node, $this->application, $this->deployment, array());

		$this->assertCommandExecuted('/rsync .* \'myserver.local:\/home\/jdoe\/app\/cache\/transfer\'/');
	}

	/**
	 * @return \TYPO3\Surf\Domain\Model\Task
	 */
	protected function createTask() {
		return new \TYPO3\Surf\Task\Transfer\RsyncTask();
	}

}
?>