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
 * A shell task for local packaging.
 *
 * It takes the following options:
 *
 * * command - The command to execute.
 * * rollbackCommand (optional) - The command to execute as a rollback.
 * * ignoreErrors (optional) - If true, ignore errors during execution. Default is true.
 * * logOutput (optional) - If true, output the log. Default is false.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\LocalShellTask', [
 *              'command' => mkdir -p /var/wwww/outerspace',
 *              'rollbackCommand' => 'rm -rf /Var/www/outerspace'
 *          ]
 *      );
 */
class LocalShellTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $replacePaths = [];
        $replacePaths['{workspacePath}'] = escapeshellarg($deployment->getWorkspacePath($application));

        if (!isset($options['command'])) {
            throw new InvalidConfigurationException('Missing "command" option for LocalShellTask', 1311168045);
        }
        $command = $options['command'];
        $command = str_replace(array_keys($replacePaths), $replacePaths, $command);

        $ignoreErrors = isset($options['ignoreErrors']) && $options['ignoreErrors'] === true;
        $logOutput = !(isset($options['logOutput']) && $options['logOutput'] === false);

        $localhost = new Node('localhost');
        $localhost->onLocalhost();

        $this->shell->executeOrSimulate($command, $localhost, $deployment, $ignoreErrors, $logOutput);
    }

    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
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
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $replacePaths = [];
        $replacePaths['{workspacePath}'] = escapeshellarg($deployment->getWorkspacePath($application));

        if (!isset($options['rollbackCommand'])) {
            return;
        }
        $command = $options['rollbackCommand'];
        $command = str_replace(array_keys($replacePaths), $replacePaths, $command);

        $localhost = new Node('localhost');
        $localhost->onLocalhost();

        $this->shell->execute($command, $localhost, $deployment, true);
    }
}
