<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Neos\Flow;

use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * This task takes care of symlinking the shared Production Configuration
 *
 * Note: this might cause problems with concurrent access due to the cached configuration
 * inside this directory.
 *
 * It takes no options
 *
 * @todo Fix problem with include cached configuration
 */
class SymlinkConfigurationTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @param array<string,mixed> $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $targetReleasePath = $deployment->getApplicationReleasePath($node);

        $context = $application instanceof Flow ? $application->getContext() : 'Production';

        $commands = [
            "cd {$targetReleasePath}/Configuration",
            "if [ -d {$context} ]; then rm -Rf {$context}; fi",
            "mkdir -p ../../../shared/Configuration/{$context}"
        ];

        if (strpos($context, '/') !== false) {
            $baseContext = dirname($context);
            $commands[] = "mkdir -p {$baseContext}";
            $commands[] = "ln -snf ../../../../shared/Configuration/{$context} {$context}";
        } else {
            $commands[] = "ln -snf ../../../shared/Configuration/{$context} {$context}";
        }

        $this->shell->executeOrSimulate($commands, $node, $deployment);
    }

    /**
     * @codeCoverageIgnore
     * @param array<string,mixed> $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
