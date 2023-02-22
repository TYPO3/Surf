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

final class UnlockDeploymentTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $deploymentLockFile = escapeshellarg(sprintf('%s/.surf/%s', $node->getDeploymentPath(), LockDeploymentTask::LOCK_FILE_NAME));

        if (!$deployment->isDryRun()) {
            $rmOptions = $deployment->getForceRun() ? ' -f' : '';
            $this->logger->info(sprintf('remove lock file %s', $deploymentLockFile));
            $this->shell->execute(sprintf('rm%1$s %2$s', $rmOptions, $deploymentLockFile), $node, $deployment);
        } else {
            $this->logger->info(sprintf('Would remove lock file %s', $deploymentLockFile));
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
