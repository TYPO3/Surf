<?php
namespace TYPO3\Surf\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A TYPO3 Flow task to run functional tests
 *
 */
class FunctionalTestTask extends \TYPO3\Surf\Domain\Model\Task
{

    /**
     * @Flow\Inject
     * @var \TYPO3\Surf\Domain\Service\ShellCommandService
     */
    protected $shell;

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if (!$application instanceof \TYPO3\Surf\Application\TYPO3\Flow) {
            throw new \TYPO3\Surf\Exception\InvalidConfigurationException(sprintf('Flow application needed for FunctionalTestTask, got "%s"', get_class($application)), 1358865890);
        }

        $targetPath = $deployment->getApplicationReleasePath($application);
        $this->shell->executeOrSimulate('cd ' . $targetPath . ' && phpunit -c Build/' . $application->getBuildEssentialsDirectoryName() . '/PhpUnit/FunctionalTests.xml', $node, $deployment);
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
