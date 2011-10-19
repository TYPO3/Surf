<?php
namespace TYPO3\Surf\Application;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use \TYPO3\Surf\Domain\Model\Workflow;
use \TYPO3\Surf\Domain\Model\Deployment;

/**
 * A FLOW3 application template
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FLOW3 extends \TYPO3\Surf\Domain\Model\Application {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct('FLOW3');
	}

	/**
	 * Register tasks for this application
	 *
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function registerTasks(Workflow $workflow, Deployment $deployment) {
		parent::registerTasks($workflow, $deployment);

		$workflow
			->forApplication($this, 'initialize', array(
				'typo3.surf:flow3:createdirectories'
			))
			->afterTask('typo3.surf:gitcheckout', array(
				'typo3.surf:flow3:symlinkdata',
				'typo3.surf:flow3:symlinkconfiguration'
			), $this)
			->afterTask('typo3.surf:gitcheckout', array(
				'typo3.surf:flow3:setfilepermissions'
			), $this)
			->forApplication($this, 'migrate', array(
				'typo3.surf:flow3:migrate'
			));
	}

}
?>