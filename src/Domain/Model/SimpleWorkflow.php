<?php
namespace TYPO3\Surf\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A simple workflow
 */
class SimpleWorkflow extends Workflow
{
    /**
     * If FALSE no rollback will be done on errors
     * @var bool
     */
    protected $enableRollback = true;

    /**
     * Order of stages that will be executed
     *
     * @var array
     */
    protected $stages = [
        // Initialize directories etc. (first time deploy)
        'initialize',
        // Local preparation of and packaging of application assets (code and files)
        'package',
        // Transfer of application assets to the node
        'transfer',
        // Update the application assets on the node
        'update',

        // Migrate (Doctrine, custom)
        'migrate',
        // Prepare final release (e.g. warmup)
        'finalize',
        // Smoke test
        'test',
        // Do symlink to current release
        'switch',
        // Delete temporary files or previous releases
        'cleanup'
    ];

    /**
     * Sequentially execute the stages for each node, so first all nodes will go through the initialize stage and
     * then the next stage will be executed until the final stage is reached and the workflow is finished.
     *
     * A rollback will be done for all nodes as long as the stage switch was not completed.
     *
     * @param Deployment $deployment
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function run(Deployment $deployment)
    {
        parent::run($deployment);

        $applications = $deployment->getApplications();
        if (count($applications) === 0) {
            throw new InvalidConfigurationException('No application configured for deployment', 1334652420);
        }

        $nodes = $deployment->getNodes();
        if (count($nodes) === 0) {
            throw new InvalidConfigurationException('No nodes configured for application', 1334652427);
        }

        foreach ($this->stages as $stage) {
            $deployment->getLogger()->notice('Stage ' . $stage);
            foreach ($nodes as $node) {
                $deployment->getLogger()->debug('Node ' . $node->getName());
                foreach ($applications as $application) {
                    if (!$application->hasNode($node)) {
                        continue;
                    }

                    $deployment->getLogger()->debug('Application ' . $application->getName());

                    try {
                        $this->executeStage($stage, $node, $application, $deployment);
                    } catch (\Exception $exception) {
                        $deployment->setStatus(Deployment::STATUS_FAILED);
                        if ($this->enableRollback) {
                            if (array_search($stage, $this->stages) <= array_search('switch', $this->stages)) {
                                $deployment->getLogger()->error('Got exception "' . $exception->getMessage() . '" rolling back.');
                                $this->taskManager->rollback();
                            } else {
                                $deployment->getLogger()->error('Got exception "' . $exception->getMessage() . '" but after switch stage, no rollback necessary.');
                                $this->taskManager->reset();
                            }
                        } else {
                            $deployment->getLogger()->error('Got exception "' . $exception->getMessage() . '" but rollback disabled. Stopping.');
                        }
                        return;
                    }
                }
            }
        }
        if ($deployment->getStatus() === Deployment::STATUS_UNKNOWN) {
            $deployment->setStatus(Deployment::STATUS_SUCCESS);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Simple workflow';
    }

    /**
     * @param bool $enableRollback
     * @return \TYPO3\Surf\Domain\Model\SimpleWorkflow
     */
    public function setEnableRollback($enableRollback)
    {
        $this->enableRollback = $enableRollback;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnableRollback()
    {
        return $this->enableRollback;
    }

    /**
     * @return array
     */
    public function getStages()
    {
        return $this->stages;
    }
}
