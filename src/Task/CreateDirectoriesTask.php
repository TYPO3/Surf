<?php

declare(strict_types=1);

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
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * A task to create initial directories and the release directory for the current release.
 *
 * This task will automatically create needed directories and create a symlink to the upcoming
 * release, called "next".
 *
 * It doesn't take any options, you have to configure the application.
 *
 * Example:
 *  $application
 *      ->setOption('deploymentPath', '/var/www/outerspace');
 */
class CreateDirectoriesTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $result = $this->shell->execute(sprintf('test -d %s', $application->getDeploymentPath()), $node, $deployment, true);
        if ($result === false) {
            throw new TaskExecutionException('Deployment directory "' . $application->getDeploymentPath() . '" does not exist on node ' . $node->getName(), 1311003253);
        }
        $commands = [
            sprintf('mkdir -p %s', $application->getReleasesPath()),
            sprintf('mkdir -p %s', $application->getSharedPath()),
            sprintf('mkdir -p %s', $deployment->getApplicationReleasePath($application)),
            sprintf('cd %s;ln -snf ./%s next', $application->getReleasesPath(), $deployment->getReleaseIdentifier())
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

    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $commands = [
            sprintf('rm %s/next', $application->getReleasesPath()),
            sprintf('rm -rf %s', $deployment->getApplicationReleasePath($application))
        ];
        $this->shell->execute($commands, $node, $deployment, true);
    }
}
