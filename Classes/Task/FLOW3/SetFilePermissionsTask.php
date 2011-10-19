<?php
namespace TYPO3\Surf\Task\FLOW3;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use \TYPO3\Surf\Domain\Model\Node;
use \TYPO3\Surf\Domain\Model\Application;
use \TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Task for setting file permissions for the FLOW3 application
 */
class SetFilePermissionsTask extends \TYPO3\Surf\Domain\Model\Task {

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
		$targetPath = $deployment->getApplicationReleasePath($application);

		$arguments = (isset($options['shellUsername']) ? $options['shellUsername'] : 'root');
		$arguments .= ' ' . (isset($options['webserverUsername']) ? $options['webserverUsername'] : 'www-data');
		$arguments .= ' ' . (isset($options['webserverGroupname']) ? $options['webserverGroupname'] : 'www-data');

		$this->shell->executeOrSimulate('cd ' . $targetPath . ' && FLOW3_CONTEXT=Production ./flow3 typo3.flow3:core:setfilepermissions ' . $arguments, $node, $deployment);
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