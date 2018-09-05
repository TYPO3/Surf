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
 * This tasks sets the file permissions for the Neos Flow application
 *
 * It takes the following options:
 *
 * * shellUsername (optional)
 * * webserverUsername (optional)
 * * webserverGroupname (optional)
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\SetFilePermissionsTask::class, [
 *              'shellUsername' => 'root',
 *              'webserverUsername' => 'www-data',
 *              'webserverGroupname' => 'www-data',
 *          ]
 *      );
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
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!$application instanceof Flow) {
            throw new InvalidConfigurationException(sprintf(
                'Flow application needed for SetFilePermissionsTask, got "%s"',
                get_class($application)
            ), 1358863436);
        }

        $targetPath = $deployment->getApplicationReleasePath($application);

        $arguments = [
            isset($options['shellUsername']) ? $options['shellUsername'] : (isset($options['username']) ? $options['username'] : 'root'),
            isset($options['webserverUsername']) ? $options['webserverUsername'] : 'www-data',
            isset($options['webserverGroupname']) ? $options['webserverGroupname'] : 'www-data'
        ];

        $this->shell->executeOrSimulate($application->buildCommand(
            $targetPath,
            'core:setfilepermissions',
            $arguments
        ), $node, $deployment);
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
    }
}
