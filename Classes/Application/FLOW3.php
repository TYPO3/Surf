<?php
namespace TYPO3\Deploy\Application;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Workflow;

/**
 * A FLOW3 application template
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FLOW3 extends \TYPO3\Deploy\Domain\Model\Application {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct('FLOW3');
	}

	/**
	 * Register tasks for this application
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Workflow $workflow
	 * @return void
	 */
	public function registerTasks(Workflow $workflow) {
		parent::registerTasks($workflow);

		$workflow
			->forApplication($this, 'initialize', array(
				'typo3.deploy:flow3:createdirectories'
			))
			->afterTask('typo3.deploy:checkout', array(
				'typo3.deploy:flow3:symlink'
			))
			->forApplication($this, 'migrate', array(
				'typo3.deploy:flow3:migrate'
			));
	}

}
?>