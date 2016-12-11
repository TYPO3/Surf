<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Task for running arbitrary TYPO3 commands
 *
 */
class RunCommandTask extends AbstractCliTask
{

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if (!$application instanceof \TYPO3\Surf\Application\TYPO3\CMS) {
            // We could make this task generic by checking for $options['scriptFileName'] here only
            throw new InvalidConfigurationException(sprintf('TYPO3 CMS application needed for RunCommandTask, got "%s"', get_class($application)), 1358863336);
        }
        if (!isset($options['command'])) {
            throw new InvalidConfigurationException('Missing option "command" for RunCommandTask', 1319201396);
        }
        $this->executeCliCommand(
            $this->getArguments($options),
            $node,
            $application,
            $deployment,
            $options
        );
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
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        // TODO Implement rollback
    }

    /**
     * @param array $options The command options
     * @return array all arguments
     */
    protected function getArguments(array $options)
    {
        $arguments = array($options['scriptFileName'], $options['command']);
        if (isset($options['arguments'])) {
            if (!is_array($options['arguments'])) {
                $options['arguments'] = array($options['arguments']);
            }
            $arguments = array_merge($arguments, $options['arguments']);
        }
        return $arguments;
    }
}
