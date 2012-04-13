<?php
namespace TYPO3\Surf\Application;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A FLOW3 application template
* @TYPO3\FLOW3\Annotations\Proxy(false)
 */
class FLOW3 extends \TYPO3\Surf\Application\BaseApplication {

	/**
	 * Constructor
	 */
	public function __construct($name = 'FLOW3') {
		parent::__construct($name);
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
			->addTask('typo3.surf:flow3:createdirectories', 'initialize', $this)
			->afterTask('typo3.surf:gitcheckout', array(
				'typo3.surf:flow3:symlinkdata',
				'typo3.surf:flow3:symlinkconfiguration',
				'typo3.surf:flow3:copyconfiguration',
				'typo3.surf:flow3:setfilepermissions'
			), $this)
			->addTask('typo3.surf:flow3:migrate', 'migrate', $this);
	}

}
?>