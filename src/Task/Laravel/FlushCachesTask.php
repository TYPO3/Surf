<?php

namespace TYPO3\Surf\Task\Laravel;

use TYPO3\Surf\Application\Laravel;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;

class FlushCachesTask extends AbstractCliTask
{
    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @throws TaskExecutionException
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!$application instanceof Laravel) {
            throw new InvalidConfigurationException(
                sprintf('Laravel application needed for %s, got "%s"', get_class($this), get_class($application)), 1358863288
            );
        }

        $this->executeCliCommand(
            ['artisan', 'cache:clear'],
            $node,
            $application,
            $deployment,
            $options
        );

        $this->executeCliCommand(
            ['artisan', 'config:clear'],
            $node,
            $application,
            $deployment,
            $options
        );

        $this->executeCliCommand(
            ['artisan', 'route:clear'],
            $node,
            $application,
            $deployment,
            $options
        );

        $this->executeCliCommand(
            ['artisan', 'view:clear'],
            $node,
            $application,
            $deployment,
            $options
        );
    }
}
