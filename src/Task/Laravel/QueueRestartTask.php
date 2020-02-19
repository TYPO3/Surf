<?php

namespace TYPO3\Surf\Task\Laravel;

use TYPO3\Surf\Application\Laravel;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;

class QueueRestartTask extends AbstractCliTask
{
    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
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
            ['artisan', 'queue:restart'],
            $node,
            $application,
            $deployment,
            $options
        );
    }
}
