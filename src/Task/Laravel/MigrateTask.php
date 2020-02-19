<?php

namespace TYPO3\Surf\Task\Laravel;

use TYPO3\Surf\Application\Laravel;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * A Neos Flow migration task
 *
 */
class MigrateTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!$application instanceof Laravel) {
            throw new InvalidConfigurationException(
                sprintf('Laravel application needed for %s, got "%s"', get_class($this), get_class($application)), 1358863288
            );
        }

        $targetPath = $deployment->getApplicationReleasePath($application);
        $this->shell->executeOrSimulate('cd ' . $targetPath . ' && php artisan migrate --force', $node, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Rollback the task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!$application instanceof Laravel) {
            throw new InvalidConfigurationException(
                sprintf('Laravel application needed for MigrateTask, got "%s"', get_class($application)), 1358863288
            );
        }

        $targetPath = $deployment->getApplicationReleasePath($application);
        $this->shell->executeOrSimulate(
            'cd ' . $targetPath . ' && php artisan migrate:rollback --force',
            $node,
            $deployment
        );
    }
}
