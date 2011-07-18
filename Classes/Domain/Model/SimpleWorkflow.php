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
	 *
	 * @param Deployment $deployment
	 * @return void
	 */
	public function run(Deployment $deployment) {
		parent::run($deployment);
		$nodes = $deployment->getNodes();
		foreach ($this->stages as $stage) {
			foreach ($nodes as $node) {
				foreach ($deployment->getApplications() as $application) {
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