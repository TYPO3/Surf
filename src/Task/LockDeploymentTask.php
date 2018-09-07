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
use TYPO3\Surf\Exception\DeploymentLockedException;

/**
 * Lock deployment task
 */
final class LockDeploymentTask extends Task implements ShellCommandServiceAwareInterface
{
    const LOCK_FILE_NAME = 'deploy.lock';

    use ShellCommandServiceAwareTrait;

    /**
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $deploymentLockFile = sprintf('%s/.surf/%s', escapeshellarg($application->getDeploymentPath()), self::LOCK_FILE_NAME);
        $locked = (bool)$this->shell->execute(sprintf('if [ -f %s ]; then echo 1; else echo 0; fi', $deploymentLockFile), $node, $deployment);
        if ($locked) {
            $currentDeploymentLockIdentifier = $this->shell->execute(sprintf('cat %s', $deploymentLockFile), $node, $deployment, true);
            throw DeploymentLockedException::deploymentLockedBy($deployment, $currentDeploymentLockIdentifier);
        }

        $this->shell->execute(sprintf('echo "%s" > %s', $deployment->getDeploymentLockIdentifier(), $deploymentLockFile), $node, $deployment, true);
    }
}
