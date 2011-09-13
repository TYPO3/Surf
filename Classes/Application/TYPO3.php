<?php
namespace TYPO3\Deploy\Application;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Workflow;
use \TYPO3\Deploy\Domain\Model\Deployment;

/**
 * A TYPO3 application template
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TYPO3 extends \TYPO3\Deploy\Domain\Model\Application {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct('TYPO3');
	}

	/**
	 * Register tasks for this application
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Workflow $workflow
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function registerTasks(Workflow $workflow, Deployment $deployment) {
		parent::registerTasks($workflow, $deployment);

		$workflow
			->forApplication($this, 'initialize', array(
				'typo3.deploy:flow3:createdirectories'
			))
			->afterTask('typo3.deploy:gitcheckout', array(
				'typo3.deploy:flow3:symlinkdata',
				'typo3.deploy:flow3:symlinkconfiguration'
			), $this)
			->afterTask('typo3.deploy:gitcheckout', array(
				'typo3.deploy:flow3:setfilepermissions'
			), $this)
			->forApplication($this, 'migrate', array(
				'typo3.deploy:flow3:migrate',
				'typo3.deploy:typo3:importsite',
			)
		);
	}

}
?>