<?php

namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * A task to execute shell commands on the remote host.
 *
 * It takes the following options:
 *
 * * command - The command that should be executed on the remote host.
 * * rollbackCommand (optional) - The command that reverses the changes.
 * * ignoreErrors (optional) - If true, ignore errors during execution. Default is true.
 * * logOutput (optional) - If true, output the log. Default is false.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\ShellTask', [
 *              'command' => 'mkdir -p /var/www/outerspace',
 *              'rollbackCommand' => 'rm -rf /var/www/outerspace'
 *          ]
 *      );
 */
class ShellTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);
        $command = $this->replacePaths($node, $application, $deployment, $options['command']);
        $this->shell->executeOrSimulate($command, $node, $deployment, $options['ignoreErrors'], $options['logOutput']);
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        if (null === $options['rollbackCommand']) {
            return;
        }

        $command = $this->replacePaths($node, $application, $deployment, $options['rollbackCommand']);
        $this->shell->execute($command, $node, $deployment, true);
    }

    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['command']);
        $resolver->setDefault('rollbackCommand', null);
        $resolver->setDefault('ignoreErrors', true);
        $resolver->setDefault('logOutput', false);
    }

    /**
     * @param string $command
     *
     * @return mixed
     */
    private function replacePaths(Node $node, Application $application, Deployment $deployment, $command)
    {
        $replacePaths = [
            '{deploymentPath}' => escapeshellarg($node->getDeploymentPath()),
            '{sharedPath}' => escapeshellarg($node->getSharedPath()),
            '{releasePath}' => escapeshellarg($deployment->getApplicationReleasePath($node)),
            '{currentPath}' => escapeshellarg($node->getReleasesPath() . '/current'),
            '{previousPath}' => escapeshellarg($node->getReleasesPath() . '/previous'),
        ];

        return str_replace(array_keys($replacePaths), $replacePaths, $command);
    }
}
