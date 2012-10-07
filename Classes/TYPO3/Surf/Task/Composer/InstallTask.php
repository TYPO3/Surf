<?php
namespace TYPO3\Surf\Task\Composer;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * Installs the composer packages based on a composer.json file in the projects root folder
 */
class InstallTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @throws TaskExecutionException
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$applicationReleasePath = $deployment->getApplicationReleasePath($application);

			// Skip if no composer.json file found
		$composerJsonExists = $this->shell->executeOrSimulate('test -f ' . \TYPO3\Flow\Utility\Files::concatenatePaths(array($applicationReleasePath, 'composer.json')), $node, $deployment, TRUE);
		if ($composerJsonExists === FALSE) {
			$deployment->getLogger()->log('No composer.json found in path ' . \TYPO3\Flow\Utility\Files::concatenatePaths(array($applicationReleasePath, 'composer.json')), LOG_DEBUG);
			return;
		}

		if (!isset($options['composerCommandPath'])) {
			throw new \TYPO3\Surf\Exception\TaskExecutionException('Composer command not found. Set the composerCommandPath option.', 1349163257);
		}

		$command = sprintf('cd %s && %s install --no-ansi --no-interaction', $applicationReleasePath, $options['composerCommandPath']);
		$this->shell->executeOrSimulate($command, $node, $deployment);
	}
}
?>
