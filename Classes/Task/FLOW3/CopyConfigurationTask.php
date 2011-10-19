<?php
namespace TYPO3\Deploy\Task\FLOW3;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Node;
use \TYPO3\Deploy\Domain\Model\Application;
use \TYPO3\Deploy\Domain\Model\Deployment;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A task for copying local configuration to the application
 *
 * The configuration directory has to exist on the target release path before
 * executing this task!
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CopyConfigurationTask extends \TYPO3\Deploy\Domain\Model\Task {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Deploy\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Executes this task
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$targetReleasePath = $deployment->getApplicationReleasePath($application);

		$username = $node->getOption('username');
		$hostname = $node->getHostname();

		$configurationPath = $this->getDeploymentConfigurationPath() . '/Configuration/' . $deployment->getName() . '/';
		$encryptedConfiguration = \TYPO3\FLOW3\Utility\Files::readDirectoryRecursively($configurationPath, 'yaml.encrypted');
		if (count($encryptedConfiguration) > 0) {
			throw new \Exception('You have sealed configuration files, please open the configuration for "' . $deployment->getName() . '"', 1317229449);
		}
		$configurations = \TYPO3\FLOW3\Utility\Files::readDirectoryRecursively($configurationPath, 'yaml');
		$commands = array();
		foreach ($configurations as $configuration) {
			$targetConfigurationPath = dirname(str_replace($configurationPath, '', $configuration));
			$commands[] = "scp {$configuration} {$username}@{$hostname}:{$targetReleasePath}/Configuration/{$targetConfigurationPath}/";
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

	/**
	 * Get the deployment configuration base path
	 *
	 * @return string
	 */
	protected function getDeploymentConfigurationPath() {
		return FLOW3_PATH_ROOT . 'Build/Deploy';
	}

}
?>