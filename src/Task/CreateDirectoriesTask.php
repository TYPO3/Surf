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

    /**
     * Executes this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $deploymentPath = $application->getDeploymentPath();
        $sharedPath = $application->getSharedPath();
        $releasesPath = $application->getReleasesPath();
        $releaseIdentifier = $deployment->getReleaseIdentifier();
        $releasePath = $deployment->getApplicationReleasePath($application);
        $result = $this->shell->execute('test -d ' . $deploymentPath, $node, $deployment, true);
        if ($result === false) {
            throw new TaskExecutionException('Deployment directory "' . $deploymentPath . '" does not exist on node ' . $node->getName(), 1311003253);
        }
        $commands = [
            'mkdir -p ' . $releasesPath,
            'mkdir -p ' . $sharedPath,
            'mkdir -p ' . $releasePath,
            'cd ' . $releasesPath . ';ln -snf ./' . $releaseIdentifier . ' next'
        ];
        $this->shell->executeOrSimulate($commands, $node, $deployment);
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
     * @todo Make the removal of a failed release configurable, sometimes it's necessary to inspect a failed release
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $releasesPath = $application->getReleasesPath();
        $releasePath = $deployment->getApplicationReleasePath($application);
        $commands = [
            'rm ' . $releasesPath . '/next',
            'rm -rf ' . $releasePath
        ];
        $this->shell->execute($commands, $node, $deployment, true);
    }
}
