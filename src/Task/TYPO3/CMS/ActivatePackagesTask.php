<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3SurfCms.SurfTools".*
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
        if (!$this->packageExists('typo3_console', $node, $application, $deployment, $options)) {
            throw new \TYPO3\Surf\Exception\InvalidConfigurationException('Extension "typo3_console" is not found! Make sure it is available in your project, or remove this task in your deployment configuration!', 1405527176);
        }
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
