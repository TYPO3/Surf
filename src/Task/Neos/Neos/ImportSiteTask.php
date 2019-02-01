<?php
namespace TYPO3\Surf\Task\Neos\Neos;

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
 * This task imports a given site into a neos project by running site:import
 *
 * This command expects a Sites.xml file to be located in the private resources directory of the given package
 * (Resources/Private/Content/Sites.xml)
 *
 * It takes the following arguments:
 *
 * * sitePackageKey (required)
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\ImportSiteTask::class, [
 *              'sitePackageKey' => 'Neos.ExampleSite'
 *          ]
 *      );
 */
class ImportSiteTask extends Task implements ShellCommandServiceAwareInterface
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
            throw new InvalidConfigurationException(sprintf('Flow application needed for ImportSiteTask, got "%s"', get_class($application)), 1358863473);
        }
        if (!isset($options['sitePackageKey'])) {
            throw new InvalidConfigurationException(sprintf('"sitePackageKey" option not set for application "%s"', $application->getName()), 1312312646);
        }

        $targetPath = $deployment->getApplicationReleasePath($application);
        $arguments = [
            '--package-key',
            $options['sitePackageKey']
        ];
        $command = 'site:import';
        $this->shell->executeOrSimulate(
            $application->buildCommand(
                $targetPath,
                $application->getCommandPackageKey($command) . ':' . $command,
                $arguments
            ),
            $node,
            $deployment
        );
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
}
