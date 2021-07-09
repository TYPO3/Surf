<?php
declare(strict_types=1);

namespace TYPO3\Surf\Task\Laravel;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * A symlink task for linking the shared storage directory
 * If the symlink target has folder, the folders themselves must exist!
 */
class SymlinkStorageTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Executes this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     *
     * @return void
     * @throws TaskExecutionException
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $targetReleasePath = $deployment->getApplicationReleasePath($application);

        $deploymentPath = $application->getDeploymentPath();
        $applicationReleasePath = $deployment->getApplicationReleasePath($application);
        $diffPath = substr($applicationReleasePath, strlen($deploymentPath));

        $relativeDataPath = str_repeat('../', substr_count(trim($diffPath, '/'), '/') + 1) . 'shared';
        $absoluteProjectRootDirectory = rtrim($targetReleasePath, '/');
        $commands = [
            'cd ' . escapeshellarg($targetReleasePath),
            sprintf('{ [ -d %1$s ] || mkdir -p %1$s ; }', escapeshellarg("{$relativeDataPath}/storage")),
            sprintf('ln -sf %1$s %2$s', escapeshellarg("{$relativeDataPath}/storage"), escapeshellarg("{$absoluteProjectRootDirectory}/storage"))
        ];
        $this->shell->executeOrSimulate($commands, $node, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     * @throws TaskExecutionException
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
