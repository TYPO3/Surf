<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * This task activates a given set of packages
 * or reads the packages from composer json and activates them
 */
class ActivatePackagesTask extends AbstractCliTask
{
    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->ensureApplicationIsTypo3Cms($application);
        if (!$this->packageExists('typo3_console', $node, $application, $deployment, $options)) {
            $deployment->getLogger()->warning('Extension "typo3_console" was not found! Make sure it is available in your project, or remove this task (' . __CLASS__ . ') in your deployment configuration!');
            return;
        }
        $deployment->getLogger()->warning('This task has been deprecated and will be removed in Surf 2.1. Please use SetUpExtensionsTask instead.');
        $activePackages = isset($options['activePackages']) ? $options['activePackages'] : array();
        foreach ($activePackages as $packageKey) {
            $this->executeCliCommand(
                array('./typo3cms', 'extension:install', $packageKey),
                $node,
                $application,
                $deployment,
                $options
            );
        }
    }
}
