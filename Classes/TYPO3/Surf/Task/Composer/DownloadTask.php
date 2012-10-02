<?php
namespace TYPO3\Surf\Task\Composer;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Downloads composer into the current releasePath.
 */
class DownloadTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @FLOW3\Inject
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

		if (isset($options['composerDownloadCommand'])) {
			$composerDownloadCommand = $options['composerDownloadCommand'];
		} else {
			$composerDownloadCommand = 'curl -s https://getcomposer.org/installer | php';
		}

		$command = sprintf('cd %s && %s', $applicationReleasePath, $composerDownloadCommand);
		$this->shell->executeOrSimulate($command, $node, $deployment);
	}
}

?>