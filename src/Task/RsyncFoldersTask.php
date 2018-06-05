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
 * A generic shell task
 */
class RsyncFoldersTask extends Task implements ShellCommandServiceAwareInterface
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
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!isset($options['folders'])) {
            return;
        }
        $folders = $options['folders'];
        if (!is_array($folders)) {
            $folders = [$folders];
        }
        $replacePaths = [
            '{deploymentPath}' => escapeshellarg($application->getDeploymentPath()),
            '{sharedPath}' => escapeshellarg($application->getSharedPath()),
            '{releasePath}' => escapeshellarg($deployment->getApplicationReleasePath($application)),
            '{currentPath}' => escapeshellarg($application->getReleasesPath() . '/current'),
            '{previousPath}' => escapeshellarg($application->getReleasesPath() . '/previous')
        ];

        $commands = [];

        $username = isset($options['username']) ? $options['username'] . '@' : '';
        $hostname = $node->getHostname();
        $port = $node->hasOption('port') ? '-P ' . escapeshellarg($node->getOption('port')) : '';

        foreach ($folders as $folderPair) {
            if (!is_array($folderPair) || count($folderPair) !== 2) {
                throw new InvalidConfigurationException('Each rsync folder definition must be an array of exactly two folders', 1405599056);
            }
            $sourceFolder = rtrim(str_replace(array_keys($replacePaths), $replacePaths, $folderPair[0]), '/') . '/';
            $targetFolder = rtrim(str_replace(array_keys($replacePaths), $replacePaths, $folderPair[1]), '/') . '/';
            $commands[] = "rsync -avz --delete -e ssh {$sourceFolder} {$username}{$hostname}:{$targetFolder}";
        }

        $ignoreErrors = isset($options['ignoreErrors']) && $options['ignoreErrors'] === true;
        $logOutput = !(isset($options['logOutput']) && $options['logOutput'] === false);

        $localhost = new Node('localhost');
        $localhost->setHostname('localhost');

        $this->shell->executeOrSimulate($commands, $localhost, $deployment, $ignoreErrors, $logOutput);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
