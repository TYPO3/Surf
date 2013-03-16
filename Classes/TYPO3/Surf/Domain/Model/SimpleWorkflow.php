<?php
namespace TYPO3\Surf\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A simple workflow
 *
 */
class SimpleWorkflow extends Workflow {

	/**
	 * If FALSE no rollback will be done on errors
	 * @var boolean
	 */
	protected $enableRollback = TRUE;

	/**
	 * Order of stages that will be executed
	 *
	 * @var array
	 */
	protected $stages = array(
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
	);

	/**
	 * Sequentially execute the stages for each node, so first all nodes will go through the initialize stage and
	 * then the next stage will be executed until the final stage is reached and the workflow is finished.
	 *
	 * A rollback will be done for all nodes as long as the stage switch was not completed.
	 *
	 * @param Deployment $deployment
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	public function run(Deployment $deployment) {
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
			$deployment->getLogger()->log('Stage ' . $stage, LOG_NOTICE);
			foreach ($nodes as $node) {
				$deployment->getLogger()->log('Node ' . $node->getName(), LOG_DEBUG);
				foreach ($applications as $application) {
					if (!$application->hasNode($node)) continue;

					$deployment->getLogger()->log('Application ' . $application->getName(), LOG_DEBUG);

					try {
						$this->executeStage($stage, $node, $application, $deployment);
					} catch(\Exception $exception) {
						$deployment->setStatus(Deployment::STATUS_FAILED);
						if ($this->enableRollback) {
							if (array_search($stage, $this->stages) <= array_search('switch', $this->stages)) {
								$deployment->getLogger()->log('Got exception "' . $exception->getMessage() . '" rolling back.', LOG_ERR);
								$this->taskManager->rollback();
							} else {
								$deployment->getLogger()->log('Got exception "' . $exception->getMessage() . '" but after switch stage, no rollback necessary.', LOG_ERR);
								$this->taskManager->reset();
							}
						} else {
							$deployment->getLogger()->log('Got exception "' . $exception->getMessage() . '" but rollback disabled. Stopping.', LOG_ERR);
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
	public function getName() {
		return 'Simple workflow';
	}

	/**
	 *
	 * @param boolean $enableRollback
	 * @return \TYPO3\Surf\Domain\Model\SimpleWorkflow
	 */
	public function setEnableRollback($enableRollback) {
		$this->enableRollback = $enableRollback;
		return $this;
	}

}
?>