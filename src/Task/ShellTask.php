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
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;

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

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     *
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);
        $command = $this->replacePaths($application, $deployment, $options['command']);
        $this->shell->executeOrSimulate($command, $node, $deployment, $options['ignoreErrors'], $options['logOutput']);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Rollback this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        if (null === $options['rollbackCommand']) {
            return;
        }

        $command = $this->replacePaths($application, $deployment, $options['rollbackCommand']);
        $this->shell->execute($command, $node, $deployment, true);
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['command']);
        $resolver->setDefault('rollbackCommand', null);
        $resolver->setDefault('ignoreErrors', true);
        $resolver->setDefault('logOutput', false);
    }

    /**
     * @param Application $application
     * @param Deployment $deployment
     * @param string $command
     *
     * @return mixed
     * @throws InvalidConfigurationException
     */
    private function replacePaths(Application $application, Deployment $deployment, $command)
    {
        $replacePaths = [
            '{deploymentPath}' => escapeshellarg($application->getDeploymentPath()),
            '{sharedPath}' => escapeshellarg($application->getSharedPath()),
            '{releasePath}' => escapeshellarg($deployment->getApplicationReleasePath($application)),
            '{currentPath}' => escapeshellarg($application->getReleasesPath() . '/current'),
            '{previousPath}' => escapeshellarg($application->getReleasesPath() . '/previous'),
        ];

        return str_replace(array_keys($replacePaths), $replacePaths, $command);
    }
}
