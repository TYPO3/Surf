<?php
namespace TYPO3\Surf\Task\Release;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Task for preparing a "TYPO3.Release" release
 */
class PrepareReleaseTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->checkOptionsForValidity($options);
        $host = $options['releaseHost'];
        $login = $options['releaseHostLogin'];
        $sitePath =  $options['releaseHostSitePath'];
        $version = $options['version'];
        $productName = $options['productName'];

        $this->shell->executeOrSimulate(sprintf('ssh %s%s "cd \"%s\" ; ./flow release:preparerelease --product-name \"%s\" --version \"%s\""', ($login ? $login . '@' : ''), $host, $sitePath, $productName, $version), $node, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Check if all required options are given
     *
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function checkOptionsForValidity(array $options)
    {
        foreach (array('releaseHost', 'releaseHostSitePath', 'version', 'productName') as $optionName) {
            if (!isset($options[$optionName])) {
                throw new InvalidConfigurationException('"' . $optionName . '" option not set', 1321549659);
            }
        }
    }
}
