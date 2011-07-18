<?php
namespace TYPO3\Deploy\Applications;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * A FLOW3 application
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FLOW3 extends \TYPO3\Deploy\Domain\Model\Application {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct('FLOW3');
	}

	/**
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Workflow $workflow 
	 */
	public function registerTasks($workflow) {
		parent::registerTasks($workflow);

		$workflow->forApplication($this, 'migrate', array(
			'typo3.deploy:flow3:migrate'
		));
	}

}
?>