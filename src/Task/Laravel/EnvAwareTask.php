<?php
declare(strict_types=1);

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

class EnvAwareTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     *
     * @throws TaskExecutionException
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
