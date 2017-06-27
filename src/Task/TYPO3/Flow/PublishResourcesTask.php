<?php
namespace TYPO3\Surf\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A Neos Flow publish resources task
 */
class PublishResourcesTask extends Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if (!$application instanceof \TYPO3\Surf\Application\TYPO3\Flow) {
            throw new InvalidConfigurationException(sprintf('Flow application needed for PublishResourcesTask, got "%s"', get_class($application)), 1425568379);
        }

        /**
         * Make sure to run the right flow command depending on current Neos version
         */
        $commandPackageKey = '';
        if ($application->getVersion() < '2.0') {
            $commandPackageKey = 'typo3.flow3:';
            $deployment->getLogger()->warning('Using commands starting with "typo3.flow3:*" have been renamed to "typo3.flow:*" Neos 2.0');
        } elseif ($application->getVersion() < '3.0') {
            $commandPackageKey = 'typo3.flow:';
            $deployment->getLogger()->warning('Using commands starting with "typo3.flow:*" have changed  Neos 3.0. Same command just remove "typo3.flow"');
        }

        $targetPath = $deployment->getApplicationReleasePath($application);
        $this->shell->executeOrSimulate('cd ' . $targetPath . ' && ' . 'FLOW_CONTEXT=' . $application->getContext() . ' ./' . $application->getFlowScriptName() . ' ' . $commandPackageKey . 'resource:publish', $node, $deployment);
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
}
