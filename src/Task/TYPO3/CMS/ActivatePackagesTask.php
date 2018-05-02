<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;

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
        try {
            $scriptFileName = $this->getConsoleScriptFileName($node, $application, $deployment, $options);
        } catch (InvalidConfigurationException $e) {
            $deployment->getLogger()->warning('TYPO3 Console script (' .$options['scriptFileName'] . ') was not found! Make sure it is available in your project, you set the "scriptFileName" option correctly or remove this task (' . __CLASS__ . ') in your deployment configuration!');
            return;
        }
        $deployment->getLogger()->warning('This task has been deprecated and will be removed in Surf 2.1. Please use SetUpExtensionsTask instead.');
        $activePackages = isset($options['activePackages']) ? $options['activePackages'] : array();
        foreach ($activePackages as $packageKey) {
            $this->executeCliCommand(
                array($scriptFileName, 'extension:activate', $packageKey),
                $node,
                $application,
                $deployment,
                $options
            );
        }
    }
}
