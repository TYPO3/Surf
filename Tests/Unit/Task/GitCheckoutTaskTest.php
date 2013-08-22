<?php
namespace TYPO3\Surf\Tests\Unit\Task;

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
 * Unit test for the GitCheckoutTask
 */
class GitCheckoutTaskTest extends BaseTaskTest {

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
	public function executeWithEmptyOptionsAndValidSha1FetchesResetsCopiesAndCleansRepository() {
		$options = array(
			'repositoryUrl' => 'ssh://git.example.com/project/path.git'
		);
		$this->responses = array(
			'git ls-remote ssh://git.example.com/project/path.git refs/heads/master | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
		);
		$this->task->execute($this->node, $this->application, $this->deployment, $options);

		$this->assertCommandExecuted('git fetch -q origin');
		$this->assertCommandExecuted('git reset -q --hard d5b7769852a5faa69574fcd3db0799f4ffbd9eec');
		$this->assertCommandExecuted('cp -RPp /home/jdoe/app/cache/transfer/. /home/jdoe/app/releases/');
		$this->assertCommandExecuted('git clean -q -d -x -ff');
	}

	/**
	 * @test
	 */
	public function executeWithBranchOptionAndValidSha1FetchesResetsAndCopiesRepository() {
		$options = array(
			'repositoryUrl' => 'ssh://git.example.com/project/path.git',
			'branch' => 'release/production'
		);
		$this->responses = array(
			'git ls-remote ssh://git.example.com/project/path.git refs/heads/release/production | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
		);
		$this->task->execute($this->node, $this->application, $this->deployment, $options);

		$this->assertCommandExecuted('git fetch -q origin');
		$this->assertCommandExecuted('git reset -q --hard d5b7769852a5faa69574fcd3db0799f4ffbd9eec');
		$this->assertCommandExecuted('cp -RPp /home/jdoe/app/cache/transfer/. /home/jdoe/app/releases/');
	}

	/**
	 * @test
	 */
	public function executeWithDisabledRecursiveSubmodulesOptionDoesNotUpdateSubmodulesRecursively() {
		$options = array(
			'repositoryUrl' => 'ssh://git.example.com/project/path.git',
			'recursiveSubmodules' => FALSE
		);
		$this->responses = array(
			'git ls-remote ssh://git.example.com/project/path.git refs/heads/master | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
		);
		$this->task->execute($this->node, $this->application, $this->deployment, $options);

		$this->assertCommandExecuted('/git submodule -q update --init (?!--recursive)/');
	}

	/**
	 * @test
	 */
	public function executeWithoutRecursiveSubmodulesOptionUpdatesSubmodulesRecursively() {
		$options = array(
			'repositoryUrl' => 'ssh://git.example.com/project/path.git'
		);
		$this->responses = array(
			'git ls-remote ssh://git.example.com/project/path.git refs/heads/master | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
		);
		$this->task->execute($this->node, $this->application, $this->deployment, $options);

		$this->assertCommandExecuted('/git submodule -q update --init --recursive/');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Surf\Exception\TaskExecutionException
	 */
	public function executeWithEmptyOptionsAndInvalidSha1ThrowsException() {
		$options = array(
			'repositoryUrl' => 'ssh://git.example.com/project/path.git'
		);
		$this->responses = array(
			'git ls-remote ssh://git.example.com/project/path.git refs/heads/master | awk \'{print $1 }\'' => 'foo-bar d5b7769852a5faa69574fcd3db0799f4ffbd9eec'
		);

		try {
			$this->task->execute($this->node, $this->application, $this->deployment, $options);
		} catch(\TYPO3\Surf\Exception\TaskExecutionException $exception) {
			$this->assertEquals(1335974926, $exception->getCode());
			throw $exception;
		}
	}

	/**
	 * @return \TYPO3\Surf\Domain\Model\Task
	 */
	protected function createTask() {
		return new \TYPO3\Surf\Task\GitCheckoutTask();
	}

}
?>