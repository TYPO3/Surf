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

/**
 * A symlink task for switching over the current directory to the new release.
 *
 * It doesn't take any options.
 */
class SymlinkReleaseTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $command = sprintf(
            'cd %s && rm -rf ./previous && if [ -e ./current ]; then mv ./current ./previous; fi && ln -s ./%s ./current && rm -rf ./next',
            $node->getReleasesPath(),
            $deployment->getReleaseIdentifier()
        );

        $this->shell->executeOrSimulate($command, $node, $deployment);
        $this->logger->notice('<success>Node "' . $node->getName() . '" ' . ($deployment->isDryRun() ? 'would be' : 'is') . ' live!</success>');
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
        $command = sprintf('cd %s && rm -f ./current && mv ./previous ./current', $node->getReleasesPath());

        $this->shell->execute($command, $node, $deployment, true);
    }
}
