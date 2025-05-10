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
use TYPO3\Surf\Domain\Enum\SimpleWorkflowStage;
use TYPO3\Surf\Domain\Service\TaskManager;
use TYPO3\Surf\Exception\DeploymentLockedException;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A simple workflow
 */
class SimpleWorkflow extends Workflow
{
    /**
     * If FALSE no rollback will be done on errors
     */
    protected bool $enableRollback = true;

    /**
     * @var array<string,string>
     */
    protected array $stages = [];

    public function __construct(TaskManager $taskManager)
    {
        parent::__construct($taskManager);
        $this->stages = SimpleWorkflowStage::toArray();
    }

    /**
     * Sequentially execute the stages for each node, so first all nodes will go through the initialize stage and
     * then the next stage will be executed until the final stage is reached and the workflow is finished.
     *
     * A rollback will be done for all nodes as long as the stage switch was not completed.
     */
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
                    } catch (DeploymentLockedException $exception) {
                        $deployment->setStatus(DeploymentStatus::CANCELLED());
                        $this->logger->info($exception->getMessage());
                        if ($this->enableRollback) {
                            $this->taskManager->rollback();
                        }

                        return;
                    } catch (Exception $exception) {
                        $deployment->setStatus(DeploymentStatus::FAILED());
                        if ($this->enableRollback) {
                            if (array_search($stage, $this->stages, false) <= array_search(SimpleWorkflowStage::STEP_09_SWITCH, $this->stages, false)) {
                                $this->logger->error('Got exception "' . $exception->getMessage() . '" rolling back.');
                                $this->taskManager->rollback();
                            } else {
                                $this->logger->error('Got exception "' . $exception->getMessage() . '" but after switch stage, no rollback necessary.');
                                $this->taskManager->reset();
                            }
                        } else {
                            $this->logger->error('Got exception "' . $exception->getMessage() . '" but rollback disabled. Stopping.');
                        }

                        return;
                    }
                }
            }
        }
        if ($deployment->getStatus()->isUnknown()) {
            $deployment->setStatus(DeploymentStatus::SUCCESS());
        }
    }

    public function getName(): string
    {
        return 'Simple workflow';
    }

    public function setEnableRollback(bool $enableRollback): self
    {
        $this->enableRollback = $enableRollback;

        return $this;
    }

    public function isEnableRollback(): bool
    {
        return $this->enableRollback;
    }

    /**
     * @return string[]
     */
    public function getStages(): array
    {
        return $this->stages;
    }
}
