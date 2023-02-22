<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task;

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
    public const LOCK_FILE_NAME = 'deploy.lock';

    private UnlockDeploymentTask $unlockDeploymentTask;

    use ShellCommandServiceAwareTrait;

    public function __construct(UnlockDeploymentTask $unlockDeploymentTask)
    {
        $this->unlockDeploymentTask = $unlockDeploymentTask;
    }

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        if (! $deployment->isDryRun()) {
            // Create .surf directory if not exists
            $lockDirectory = escapeshellarg($node->getDeploymentPath() . '/.surf');
            $this->shell->execute(sprintf('[ -d %1$s ] || mkdir %1$s', $lockDirectory), $node, $deployment);
        }

        $deploymentLockFile = escapeshellarg(sprintf('%s/.surf/%s', $node->getDeploymentPath(), self::LOCK_FILE_NAME));
        $locked = (bool)$this->shell->execute(sprintf('if [ -f %s ]; then echo 1; else echo 0; fi', $deploymentLockFile), $node, $deployment);
        if ($locked) {
            $currentDeploymentLockIdentifier = $this->shell->execute(sprintf('cat %s', $deploymentLockFile), $node, $deployment);
            throw DeploymentLockedException::deploymentLockedBy($deployment, $currentDeploymentLockIdentifier);
        }

        if (! $deployment->isDryRun()) {
            $this->shell->execute(sprintf('echo %s > %s', escapeshellarg($deployment->getDeploymentLockIdentifier()), $deploymentLockFile), $node, $deployment);
        } else {
            $this->logger->info(sprintf('Create lock file %s with identifier %s', $deploymentLockFile, $deployment->getDeploymentLockIdentifier()));
        }
    }

    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->unlockDeploymentTask->execute($node, $application, $deployment, $options);
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
