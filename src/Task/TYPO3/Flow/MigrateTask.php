<?php
namespace TYPO3\Surf\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A Neos Flow migration task
 *
 */
class MigrateTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if (!$application instanceof \TYPO3\Surf\Application\TYPO3\Flow) {
            throw new \TYPO3\Surf\Exception\InvalidConfigurationException(sprintf('Flow application needed for MigrateTask, got "%s"', get_class($application)), 1358863288);
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
        $this->shell->executeOrSimulate('cd ' . $targetPath . ' && FLOW_CONTEXT=' . $application->getContext() . ' ./' . $application->getFlowScriptName() . ' ' . $commandPackageKey . 'doctrine:migrate', $node, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
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
        // TODO Implement rollback of Doctrine migration
    }
}
