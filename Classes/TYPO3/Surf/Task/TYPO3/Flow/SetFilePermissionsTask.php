<?php
namespace TYPO3\Surf\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * Task for setting file permissions for the TYPO3 Flow application
 */
class SetFilePermissionsTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
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
		if (!$application instanceof \TYPO3\Surf\Application\TYPO3\Flow) {
			throw new \TYPO3\Surf\Exception\InvalidConfigurationException(sprintf('Flow application needed for SetFilePermissionsTask, got "%s"', get_class($application)), 1358863436);
		}

		$targetPath = $deployment->getApplicationReleasePath($application);

		$arguments = isset($options['shellUsername']) ? $options['shellUsername'] : (isset($options['username']) ? $options['username'] : 'root');
		$arguments .= ' ' . (isset($options['webserverUsername']) ? $options['webserverUsername'] : 'www-data');
		$arguments .= ' ' . (isset($options['webserverGroupname']) ? $options['webserverGroupname'] : 'www-data');

		$commandPackageKey = 'typo3.flow';
		if ($application->getVersion() < '2.0') {
			$commandPackageKey = 'typo3.flow3';
		}

		$this->shell->executeOrSimulate('cd ' . $targetPath . ' && FLOW_CONTEXT=' . $application->getContext() . ' ./' . $application->getFlowScriptName() . ' ' . $commandPackageKey . ':core:setfilepermissions ' . $arguments, $node, $deployment);
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
	 * Rollback the task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array()) {
	}

}
?>