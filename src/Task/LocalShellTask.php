<?php
namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A shell task for local packaging
 */
class LocalShellTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

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
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $replacePaths = array();
        $replacePaths['{workspacePath}'] = escapeshellarg($deployment->getWorkspacePath($application));

        if (!isset($options['command'])) {
            throw new InvalidConfigurationException('Missing "command" option for LocalShellTask', 1311168045);
        }
        $command = $options['command'];
        $command = str_replace(array_keys($replacePaths), $replacePaths, $command);

        $ignoreErrors = isset($options['ignoreErrors']) && $options['ignoreErrors'] === true;
        $logOutput = !(isset($options['logOutput']) && $options['logOutput'] === false);

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
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Rollback this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $replacePaths = array();
        $replacePaths['{workspacePath}'] = escapeshellarg($deployment->getWorkspacePath($application));

        if (!isset($options['rollbackCommand'])) {
            return;
        }
        $command = $options['rollbackCommand'];
        $command = str_replace(array_keys($replacePaths), $replacePaths, $command);

        $localhost = new Node('localhost');
        $localhost->setHostname('localhost');

        $this->shell->execute($command, $localhost, $deployment, true);
    }
}
