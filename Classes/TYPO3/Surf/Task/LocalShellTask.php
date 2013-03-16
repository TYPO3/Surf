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
 * A shell task for local packaging
 */
class LocalShellTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Executes this task
	 *
	 * Options:
	 *   command: The command to execute
	 *   rollbackCommand: The command to execute as a rollback (optional)
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$replacePaths = array();
		$workspacePath = $deployment->getWorkspacePath($application);
		$replacePaths['{workspacePath}'] = $workspacePath;

		if (!isset($options['command'])) {
			throw new \TYPO3\Surf\Exception\InvalidConfigurationException('Missing "command" option for LocalShellTask', 1311168045);
		}
		$command = $options['command'];
		$command = str_replace(array_keys($replacePaths), $replacePaths, $command);

		$ignoreErrors = isset($options['ignoreErrors']) && $options['ignoreErrors'] === TRUE;
		$logOutput = !(isset($options['logOutput']) && $options['logOutput'] === FALSE);

		$localhost = new Node('localhost');
		$localhost->setHostname('localhost');

		$this->shell->executeOrSimulate($command, $localhost, $deployment, $ignoreErrors, $logOutput);
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
	 */
	public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$replacePaths = array();
		$workspacePath = $deployment->getWorkspacePath($application);
		$replacePaths['{workspacePath}'] = $workspacePath;

		if (!isset($options['rollbackCommand'])) {
			return;
		}
		$command = $options['rollbackCommand'];
		$command = str_replace(array_keys($replacePaths), $replacePaths, $command);

		$localhost = new Node('localhost');
		$localhost->setHostname('localhost');

		$this->shell->execute($command, $localhost, $deployment, TRUE);
	}

}
?>