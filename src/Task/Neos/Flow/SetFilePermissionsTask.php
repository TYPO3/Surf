<?php
namespace TYPO3\Surf\Task\Neos\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Task for setting file permissions for the Neos Flow application
 */
class SetFilePermissionsTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if (!$application instanceof Flow) {
            throw new InvalidConfigurationException(sprintf('Flow application needed for SetFilePermissionsTask, got "%s"', get_class($application)), 1358863436);
        }

        $targetPath = $deployment->getApplicationReleasePath($application);

        $arguments = isset($options['shellUsername']) ? $options['shellUsername'] : (isset($options['username']) ? $options['username'] : 'root');
        $arguments .= ' ' . (isset($options['webserverUsername']) ? $options['webserverUsername'] : 'www-data');
        $arguments .= ' ' . (isset($options['webserverGroupname']) ? $options['webserverGroupname'] : 'www-data');

        $commandPackageKey = 'typo3.flow';
        if ($application->getVersion() < '2.0') {
            $commandPackageKey = 'typo3.flow3';
        }

        $this->shell->executeOrSimulate('cd ' . $targetPath . ' && FLOW_CONTEXT=' . $application->getContext() . ' ./' . $application->getFlowScriptName() . ' ' . $commandPackageKey . ':core:setfilepermissions ' . $arguments, $node, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array())
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
     * @return void
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
    }
}
