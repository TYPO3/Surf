<?php

namespace TYPO3\Surf\Task\Neos\Flow;

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
use Webmozart\Assert\Assert;

/**
 * This Task runs functional tests for a flow application
 *
 * It takes no options
 */
class FunctionalTestTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application|Flow $application
     * @param Deployment $deployment
     * @param array $options
     *
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        Assert::isInstanceOf($application, Flow::class, sprintf('Flow application needed for FunctionalTestTask, got "%s"', get_class($application)));

        $targetPath = $deployment->getApplicationReleasePath($application);

        $command = sprintf('cd %s && phpunit -c Build/%s/PhpUnit/FunctionalTests.xml', $targetPath, $application->getBuildEssentialsDirectoryName());

        $this->shell->executeOrSimulate($command, $node, $deployment);
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
