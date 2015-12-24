<?php
namespace TYPO3\Surf\Task;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Exception\InvalidConfigurationException;

use TYPO3\Flow\Annotations as Flow;

/**
 * Task for uploading to sourceforge
 *
 */
class SourceforgeUploadTask extends \TYPO3\Surf\Domain\Model\Task {

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
		$this->checkOptionsForValidity($options);
		$projectName = $options['sourceforgeProjectName'];

		$sourceforgeLogin = $options['sourceforgeUserName'] . ',' . $options['sourceforgeProjectName'];

		$projectDirectory = str_replace(' ', '\ ', sprintf('/home/frs/project/%s/%s/%s/%s/%s', substr($projectName, 0, 1), substr($projectName, 0, 2), $projectName, $options['sourceforgePackageName'], $options['version']));
		$targetHostAndDirectory = escapeshellarg($sourceforgeLogin . '@frs.sourceforge.net:' . $projectDirectory);

		$this->shell->executeOrSimulate('rsync -e ssh ' . implode(' ', $options['files']) . ' ' . $targetHostAndDirectory, $node, $deployment);
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
	 * Check if all required options are given
	 *
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	protected function checkOptionsForValidity(array $options) {
		if (!isset($options['sourceforgeProjectName'])) {
			throw new InvalidConfigurationException('"sourceforgeProjectName" option not set', 1314170122);
		}

		if (!isset($options['sourceforgePackageName'])) {
			throw new InvalidConfigurationException('"sourceforgePackageName" option not set', 1314170132);
		}

		if (!isset($options['sourceforgeUserName'])) {
			throw new InvalidConfigurationException('"sourceforgeUserName" option not set', 1314170145);
		}

		if (!isset($options['version'])) {
			throw new InvalidConfigurationException('"version" option not set', 1314170151);
		}

		if (!isset($options['files'])) {
			throw new InvalidConfigurationException('"files" option for upload not set', 1314170162);
		}

		if (!is_array($options['files'])) {
			throw new InvalidConfigurationException('"files" option for upload is not an array', 1314170175);
		}
	}
}
?>