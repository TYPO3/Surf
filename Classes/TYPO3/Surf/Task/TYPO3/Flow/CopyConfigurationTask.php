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
 * A task for copying local configuration to the application
 *
 * The configuration directory has to exist on the target release path before
 * executing this task!
 */
class CopyConfigurationTask extends \TYPO3\Surf\Domain\Model\Task {

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
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		if (!isset($options['username']) && !$node->isLocalhost()) {
			throw new \TYPO3\Surf\Exception\InvalidConfigurationException(sprintf('Missing "username" option for node "%s"', $node->getName()), 1348844231);
		}

		$targetReleasePath = $deployment->getApplicationReleasePath($application);
		$configurationPath = $deployment->getDeploymentConfigurationPath() . '/';
		if (!is_dir($configurationPath)) {
			return;
		}

		$encryptedConfiguration = \TYPO3\Flow\Utility\Files::readDirectoryRecursively($configurationPath, 'yaml.encrypted');
		if (count($encryptedConfiguration) > 0) {
			throw new \TYPO3\Surf\Exception\TaskExecutionException('You have sealed configuration files, please open the configuration for "' . $deployment->getName() . '"', 1317229449);
		}
		$configurations = \TYPO3\Flow\Utility\Files::readDirectoryRecursively($configurationPath, 'yaml');
		$commands = array();
		foreach ($configurations as $configuration) {
			$targetConfigurationPath = dirname(str_replace($configurationPath, '', $configuration));
			if ($node->isLocalhost()) {
				$commands[] = "cp {$configuration} {$targetReleasePath}/Configuration/{$targetConfigurationPath}/";
			} else {
				$username = $options['username'];
				$hostname = $node->getHostname();
				$port = $node->hasOption('port') ? '-P ' . escapeshellarg($node->getOption('port')) : '';
				$commands[] = "scp {$port} {$configuration} {$username}@{$hostname}:{$targetReleasePath}/Configuration/{$targetConfigurationPath}/";
			}
		}

		$localhost = new Node('localhost');
		$localhost->setHostname('localhost');

		$this->shell->executeOrSimulate($commands, $localhost, $deployment);
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
