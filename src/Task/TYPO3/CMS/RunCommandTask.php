<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\TYPO3\CMS;
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
        $this->ensureApplicationIsTypo3Cms($application);
        if (!isset($options['command'])) {
            throw new InvalidConfigurationException('Missing option "command" for RunCommandTask', 1319201396);
        }
        $this->executeCliCommand(
            $this->getArguments($node, $application, $deployment, $options),
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
    protected function getArguments(Node $node, CMS $application, Deployment $deployment, array $options = array())
    {
        $arguments = array($this->getConsoleScriptFileName($node, $application, $deployment, $options), $options['command']);
        if (isset($options['arguments'])) {
            if (!is_array($options['arguments'])) {
                $options['arguments'] = array($options['arguments']);
            }
            $arguments = array_merge($arguments, $options['arguments']);
        }
        return $arguments;
    }
}
