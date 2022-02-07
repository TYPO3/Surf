<?php

namespace TYPO3\Surf\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Exception;
use TYPO3\Surf\Domain\Enum\DeploymentStatus;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Generic\RollbackTask;

final class RollbackWorkflow extends Workflow
{
    /**
     * Order of stages that will be executed
     */
    private array $stages = [
        'rollback:initialize',
        'rollback:execute',
        'rollback:cleanup',
    ];

    public function run(Deployment $deployment): void
    {
        parent::run($deployment);

        $applications = $deployment->getApplications();
        if (count($applications) === 0) {
            throw InvalidConfigurationException::createNoApplicationConfigured();
        }

        $nodes = $deployment->getNodes();
        if (count($nodes) === 0) {
            throw InvalidConfigurationException::createNoNodesConfigured();
        }

        $this->configureRollbackTasks($deployment);

        foreach ($this->stages as $stage) {
            $deployment->getLogger()->notice('Stage ' . $stage);
            foreach ($nodes as $node) {
                $deployment->getLogger()->debug('Node ' . $node->getName());
                foreach ($applications as $application) {
                    if (! $application->hasNode($node)) {
                        continue;
                    }

                    $deployment->getLogger()->debug('Application ' . $application->getName());

                    try {
                        $this->executeStage($stage, $node, $application, $deployment);
                    } catch (Exception $exception) {
                        return;
                    }
                }
            }
        }
        if ($deployment->getStatus()->isUnknown()) {
            $deployment->setStatus(DeploymentStatus::SUCCESS());
        }
    }

    private function configureRollbackTasks(Deployment $deployment): void
    {
        foreach ($deployment->getNodes() as $node) {
            foreach ($deployment->getApplications() as $application) {
                if (! $application->hasNode($node)) {
                    continue;
                }

                $this->addTaskToStage(RollbackTask::class, 'rollback:execute', $application);
            }
        }
    }

    public function getName(): string
    {
        return 'Rollback workflow';
    }
}
