<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Model;

use Exception;
use TYPO3\Surf\Domain\Enum\DeploymentStatus;
use TYPO3\Surf\Domain\Enum\RollbackWorkflowStage;
use TYPO3\Surf\Domain\Service\TaskManager;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Generic\RollbackTask;

final class RollbackWorkflow extends Workflow
{
    private array $stages;

    public function __construct(TaskManager $taskManager)
    {
        parent::__construct($taskManager);
        $this->stages = RollbackWorkflowStage::toArray();
    }

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
            $this->logger->notice('Stage ' . $stage);
            foreach ($nodes as $node) {
                $this->logger->debug('Node ' . $node->getName());
                foreach ($applications as $application) {
                    if (! $application->hasNode($node)) {
                        continue;
                    }

                    $this->logger->debug('Application ' . $application->getName());

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

                $this->addTaskToStage(RollbackTask::class, RollbackWorkflowStage::STEP_02_EXECUTE, $application);
            }
        }
    }

    public function getName(): string
    {
        return 'Rollback workflow';
    }
}
