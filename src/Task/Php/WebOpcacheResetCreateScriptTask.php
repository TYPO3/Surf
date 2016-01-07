<?php
namespace TYPO3\Surf\Task\Php;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * Create a script to reset the PHP opcache
 *
 * The task creates a temporary script (locally in the release workspace directory) for resetting the PHP opcache in a
 * later web request. A secondary task will execute an HTTP request and thus execute the script.
 *
 * The opcache reset has to be done in the webserver process, so a simple CLI command would not help.
 */
class WebOpcacheResetCreateScriptTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

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
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $workspacePath = $deployment->getWorkspacePath($application);
        $scriptBasePath = isset($options['scriptBasePath']) ? $options['scriptBasePath'] : $workspacePath . '/Web';

        if (!isset($options['scriptIdentifier'])) {
            // Generate random identifier
            $factory = new \RandomLib\Factory;
            $generator = $factory->getMediumStrengthGenerator();
            $scriptIdentifier = $generator->generateString(32);

            // Store the script identifier as an application option
            $application->setOption('TYPO3\\Surf\\Task\\Php\\WebOpcacheResetExecuteTask[scriptIdentifier]', $scriptIdentifier);
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

            if ($result === false) {
                throw new \TYPO3\Surf\Exception\TaskExecutionException('Could not write file "' . $scriptFilename . '"', 1421932414);
            }
        }
    }
}
