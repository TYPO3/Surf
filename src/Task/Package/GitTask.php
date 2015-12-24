<?php
namespace TYPO3\Surf\Task\Package;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Exception\TaskExecutionException;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Surf\Task\Git\AbstractCheckoutTask;

/**
 * A Git package task
 *
 * Package an application by doing a local git update / clone before using the configured "transferMethod" to transfer
 * assets to the application node(s).
 */
class GitTask extends AbstractCheckoutTask {

	/**
	 * Execute this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		if (!isset($options['repositoryUrl'])) {
			throw new \TYPO3\Surf\Exception\InvalidConfigurationException(sprintf('Missing "repositoryUrl" option for application "%s"', $application->getName()), 1374074052);
		}

		$localCheckoutPath = $deployment->getWorkspacePath($application);

		$node = $deployment->getNode('localhost');

		$sha1 = $this->executeOrSimulateGitCloneOrUpdate($localCheckoutPath, $node, $deployment, $options);

		$this->executeOrSimulatePostGitCheckoutCommands($localCheckoutPath, $sha1, $node, $deployment, $options);
	}

}
?>