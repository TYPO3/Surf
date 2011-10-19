<?php
namespace TYPO3\Surf\Task\FLOW3;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A task to create FLOW3 specific directories
 *
 */
class CreateDirectoriesTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$deploymentPath = $application->getDeploymentPath();
		$this->shell->executeOrSimulate(array(
			'mkdir -p ' . $deploymentPath . '/shared/Data/Logs',
			'mkdir -p ' . $deploymentPath . '/shared/Data/Persistent',
			'mkdir -p ' . $deploymentPath . '/shared/Configuration'
		), $node, $deployment);
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

}
?>