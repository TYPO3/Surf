<?php

namespace TYPO3\Surf\Task\Laravel;

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
 * Copy .env into release path.
 *
 * It takes no options
 */
class EnvAwareTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     *
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $sharedPath = $application->getSharedPath();
        $releasePath = $deployment->getApplicationReleasePath($application);

        $result = $this->shell->execute(sprintf('test -f %s/.env', $sharedPath), $node, $deployment, true);
        if ($result === false) {
            throw new TaskExecutionException('.env file in "' . $sharedPath . '" does not exist on node ' . $node->getName(), 1582080037);
        }

        $command = sprintf(
            'cp %s %s',
            escapeshellarg($sharedPath . '/.env'),
            escapeshellarg($releasePath . '/.env')
        );

        $this->shell->executeOrSimulate($command, $node, $deployment);
    }
}
