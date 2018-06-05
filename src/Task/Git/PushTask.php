<?php
namespace TYPO3\Surf\Task\Git;

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
 * A task which can push to a git remote
 */
class PushTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * Options:
     *   remote: The git remote to use
     *   refspec: The refspec to push
     *   recurseIntoSubmodules: If true, push submodules as well (optional)
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!isset($options['remote'])) {
            throw new InvalidConfigurationException('Missing "remote" option for PushTask', 1314186541);
        }

        if (!isset($options['refspec'])) {
            throw new InvalidConfigurationException('Missing "refspec" option for PushTask', 1314186553);
        }

        $targetPath = $deployment->getApplicationReleasePath($application);

        $this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git push -f %s %s', $options['remote'], $options['refspec']), $node, $deployment);
        if (isset($options['recurseIntoSubmodules']) && $options['recurseIntoSubmodules'] === true) {
            $this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git submodule foreach \'git push -f %s %s\'', $options['remote'], $options['refspec']), $node, $deployment);
        }
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
}
