<?php
namespace TYPO3\Deploy\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Deployment;
use \TYPO3\Deploy\Domain\Model\Node;

/**
 * A simple workflow
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SimpleWorkflow extends Workflow {

	/**
	 * Order of stages that will be executed
	 *
	 * @var array
	 */
	protected $stages = array(
		// Initialize directories etc. (first time deploy)
		'initialize',
		// Updates code
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
	 */
	public function run(Deployment $deployment) {
		parent::run($deployment);
		$nodes = $deployment->getNodes();
		foreach ($this->stages as $stage) {
			$deployment->getLogger()->log('====== Stage ' . $stage . ' ======', LOG_DEBUG);
			foreach ($nodes as $node) {
				$deployment->getLogger()->log('**** Node ' . $node->getName() . ' ****', LOG_DEBUG);
				foreach ($deployment->getApplications() as $application) {
					if (!$application->hasNode($node)) continue;

					$deployment->getLogger()->log('* Application ' . $application->getName() . ' *', LOG_DEBUG);

					try {
						$this->executeStage($stage, $node, $application, $deployment);
					} catch(\Exception $exception) {
						if (array_search($stage, $this->stages) <= array_search('switch', $this->stages)) {
							$deployment->getLogger()->log('Got exception "' . $exception->getMessage() . '" rolling back.', LOG_ERR);
							$this->taskManager->rollback();
						} else {
							$deployment->getLogger()->log('Got exception "' . $exception->getMessage() . '" but after switch stage, no rollback necessary.', LOG_ERR);
							$this->taskManager->reset();
						}
						return;
					}
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'Simple workflow';
	}

}
?>