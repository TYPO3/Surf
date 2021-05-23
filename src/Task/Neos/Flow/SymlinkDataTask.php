<?php
namespace TYPO3\Surf\Task\Neos\Flow;

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
 * This task takes care of symlinking the shared Data
 *
 * It takes no options
 */
class SymlinkDataTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $releaseIdentifier = $deployment->getReleaseIdentifier();
        $releasesPath = $node->getReleasesPath();
        $commands = [
            "mkdir -p $releasesPath/$releaseIdentifier/Data",
            "cd $releasesPath/$releaseIdentifier",
            'ln -sf ../../../shared/Data/Logs ./Data/Logs',
            'ln -sf ../../../shared/Data/Persistent ./Data/Persistent'
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
}
