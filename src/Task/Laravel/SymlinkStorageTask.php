<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Laravel;

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * A symlink task for linking the shared storage directory
 * If the symlink target has folder, the folders themselves must exist!
 */
class SymlinkStorageTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

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
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
