<?php
namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Git\AbstractCheckoutTask;

/**
 * A Git checkout task.
 *
 * It takes the following options:
 *
 * * repositoryUrl - The repository to check out.
 * * hardClean (optional) - If true, the task performs a hard clean. Default is true.
 *
 * Example:
 *  $application->setOption('repositoryUrl', 'git@github.com:TYPO3/Surf.git');
 */
class GitCheckoutTask extends AbstractCheckoutTask
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!isset($options['repositoryUrl'])) {
            throw new InvalidConfigurationException(sprintf('Missing "repositoryUrl" option for application "%s"', $application->getName()), 1335974764);
        }

        $releasePath = $deployment->getApplicationReleasePath($application);
        $checkoutPath = Files::concatenatePaths([$application->getDeploymentPath(), 'cache/transfer']);

        if (!isset($options['hardClean'])) {
            $options['hardClean'] = true;
        }

        $sha1 = $this->executeOrSimulateGitCloneOrUpdate($checkoutPath, $node, $deployment, $options);

        $command = strtr("
            cp -RPp $checkoutPath/. $releasePath
                && (echo $sha1 > $releasePath" . 'REVISION)
            ', "\t\n", '  ');

        $this->shell->executeOrSimulate($command, $node, $deployment);

        $this->executeOrSimulatePostGitCheckoutCommands($releasePath, $sha1, $node, $deployment, $options);
    }

    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $releasePath = $deployment->getApplicationReleasePath($application);
        $this->shell->execute('rm -f ' . $releasePath . 'REVISION', $node, $deployment, true);
    }
}
