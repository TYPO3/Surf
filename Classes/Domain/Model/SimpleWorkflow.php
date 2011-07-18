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
		// Delete temporary
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
		foreach ($nodes as $node) {
			foreach ($this->stages as $stage) {
				foreach ($deployment->getApplications() as $application) {
					// TODO Catch exceptions and do the transaction thingy
					$this->executeStage($stage, $node, $application, $deployment);
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'Single node workflow';
	}

}
?>