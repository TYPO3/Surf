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

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $result = $this->shell->execute(sprintf('test -d %s', $node->getDeploymentPath()), $node, $deployment, true);
        if ($result === false) {
            throw new TaskExecutionException('Deployment directory "' . $node->getDeploymentPath() . '" does not exist on node ' . $node->getName(), 1311003253);
        }
        $commands = [
            sprintf('mkdir -p %s', $node->getReleasesPath()),
            sprintf('mkdir -p %s', $node->getSharedPath()),
            sprintf('mkdir -p %s', $deployment->getApplicationReleasePath($node)),
            sprintf('cd %s;ln -snf ./%s next', $node->getReleasesPath(), $deployment->getReleaseIdentifier())
        ];
        $this->shell->executeOrSimulate($commands, $node, $deployment);
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $commands = [
            sprintf('rm %s/next', $node->getReleasesPath()),
            sprintf('rm -rf %s', $deployment->getApplicationReleasePath($node))
        ];
        $this->shell->execute($commands, $node, $deployment, true);
    }
}
