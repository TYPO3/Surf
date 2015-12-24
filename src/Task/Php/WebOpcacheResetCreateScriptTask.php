<?php
namespace TYPO3\Surf\Task\Php;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * Create a script to reset the PHP opcache
 *
 * The task creates a temporary script (locally in the release workspace directory) for resetting the PHP opcache in a
 * later web request. A secondary task will execute an HTTP request and thus execute the script.
 *
 * The opcache reset has to be done in the webserver process, so a simple CLI command would not help.
 */
class WebOpcacheResetCreateScriptTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shellCommandService;

	/**
	 * Execute this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options Supported options: "scriptBasePath" and "scriptIdentifier"
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$workspacePath = $deployment->getWorkspacePath($application);
		$scriptBasePath = isset($options['scriptBasePath']) ? $options['scriptBasePath'] : \TYPO3\Flow\Utility\Files::concatenatePaths(array($workspacePath, 'Web'));

		if (!isset($options['scriptIdentifier'])) {
			// Generate random identifier
			$scriptIdentifier = \TYPO3\Flow\Utility\Algorithms::generateRandomString(32);

			// Store the script identifier as an application option
			$application->setOption('typo3.surf:php:webopcacheresetexecute[scriptIdentifier]', $scriptIdentifier);
		} else {
			$scriptIdentifier = $options['scriptIdentifier'];
		}

		$localhost = new Node('localhost');
		$localhost->setHostname('localhost');

		$commands = array(
			'cd ' . escapeshellarg($scriptBasePath),
			'rm -f surf-opcache-reset-*'
		);

		$this->shellCommandService->executeOrSimulate($commands, $localhost, $deployment);

		if (!$deployment->isDryRun()) {
			$scriptFilename = $scriptBasePath . '/surf-opcache-reset-' . $scriptIdentifier . '.php';
			$result = file_put_contents($scriptFilename, '<?php
				if (function_exists("opcache_reset")) {
					opcache_reset();
				}
				@unlink(__FILE__);
				echo "success";
			');

			if ($result === FALSE) {
				throw new \TYPO3\Surf\Exception\TaskExecutionException('Could not write file "' . $scriptFilename . '"', 1421932414);
			}
		}
	}

}
?>