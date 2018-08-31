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

/**
 * Task for doing a "TYPO3.Release" release
 */
class ReleaseTask extends PrepareReleaseTask
{

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        $host = $options['releaseHost'];
        $login = $options['releaseHostLogin'];
        $changeLogUri = $options['changeLogUri'];
        $sitePath =  $options['releaseHostSitePath'];
        $version = $options['version'];
        $productName = $options['productName'];

        $this->shell->executeOrSimulate(sprintf('ssh %s%s "cd \"%s\" ; ./flow release:release --product-name \"%s\" --version \"%s\" --change-log-uri \"%s\""', ($login ? $login . '@' : ''), $host, $sitePath, $productName, $version, ($changeLogUri ? $changeLogUri : '')), $node, $deployment);
    }
}
