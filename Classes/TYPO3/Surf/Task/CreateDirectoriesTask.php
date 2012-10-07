<?php
namespace TYPO3\Surf\Task;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * A task to create initial directories and the release directory for the current release
 *
 * This task will automatically create needed directories and create a symlink to the upcoming
 * release, called "next".
 */
class CreateDirectoriesTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Executes this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$deploymentPath = $application->getDeploymentPath();
		$sharedPath = $application->getSharedPath();
		$releasesPath = $deploymentPath . '/releases';
		$releaseIdentifier = $deployment->getReleaseIdentifier();
		$releasePath = $deployment->getApplicationReleasePath($application);
		$result = $this->shell->execute('test -d ' . $deploymentPath, $node, $deployment, TRUE);
		if ($result === FALSE) {
			throw new \TYPO3\Surf\Exception\TaskExecutionException('Deployment directory "' . $deploymentPath . '" does not exist on node ' . $node->getName(), 1311003253);
		}
		$commands = array(
			'mkdir -p ' . $releasesPath,
			'mkdir -p ' . $sharedPath,
			'mkdir -p ' . $releasePath,
			'cd ' .  $releasesPath . ';ln -snf ./' . $releaseIdentifier . ' next'
		);
		$this->shell->executeOrSimulate($commands, $node, $deployment);
	}

	/**
	 * Simulate this task
	 *
	 * @param Node $node
	 * @param Application $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$this->execute($node, $application, $deployment, $options);
	}

	/**
	 * Rollback this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 * @todo Make the removal of a failed release configurable, sometimes it's necessary to inspect a failed release
	 */
	public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$deploymentPath = $application->getDeploymentPath();
		$releasesPath = $deploymentPath . '/releases';
		$releasePath = $deployment->getApplicationReleasePath($application);
		$commands = array(
			'rm ' . $releasesPath . '/next',
			'rm -rf ' . $releasePath
		);
		$this->shell->execute($commands, $node, $deployment, TRUE);
	}

}
?>