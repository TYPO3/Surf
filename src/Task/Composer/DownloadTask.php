<?php
namespace TYPO3\Surf\Task\Composer;

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

/**
 * Downloads composer into the current releasePath.
 */
class DownloadTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $applicationReleasePath = $deployment->getApplicationReleasePath($application);

        if (isset($options['composerDownloadCommand'])) {
            $composerDownloadCommand = $options['composerDownloadCommand'];
        } else {
            $composerDownloadCommand = 'curl -s https://getcomposer.org/installer | php';
        }

        $command = sprintf('cd %s && %s', escapeshellarg($applicationReleasePath), $composerDownloadCommand);
        $this->shell->executeOrSimulate($command, $node, $deployment);
    }
}
