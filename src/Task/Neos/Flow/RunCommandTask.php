<?php
namespace TYPO3\Surf\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Task for running arbitrary Neos Flow commands
 */
class RunCommandTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!$application instanceof Flow) {
            throw new InvalidConfigurationException(sprintf('Flow application needed for RunCommandTask, got "%s"', get_class($application)), 1358863336);
        }
        if (!isset($options['command'])) {
            throw new InvalidConfigurationException('Missing option "command" for RunCommandTask', 1319201396);
        }

        $ignoreErrors = isset($options['ignoreErrors']) && $options['ignoreErrors'] === true;
        $logOutput = !(isset($options['logOutput']) && $options['logOutput'] === false);
        $targetPath = $deployment->getApplicationReleasePath($application);
        $arguments = $this->buildCommandArguments($options);
        $command = 'cd ' . $targetPath . ' && FLOW_CONTEXT=' . $application->getContext() . ' ./' . $application->getFlowScriptName() . ' ' . $options['command'] . $arguments;
        $this->shell->executeOrSimulate($command, $node, $deployment, $ignoreErrors, $logOutput);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Rollback the task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        // TODO Implement rollback
    }

    /**
     * @param array $options The command options
     * @return string The escaped arguments string
     */
    protected function buildCommandArguments(array $options)
    {
        $arguments = '';
        if (isset($options['arguments'])) {
            if (!is_array($options['arguments'])) {
                $options['arguments'] = [$options['arguments']];
            }

            $options['arguments'] = array_map(function ($value) {
                return escapeshellarg($value);
            }, $options['arguments']);

            $arguments = ' ' . implode(' ', $options['arguments']);
        }
        return $arguments;
    }
}
