<?php
namespace TYPO3\Surf\Application\TYPO3;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A TYPO3 Flow application template
* @TYPO3\Flow\Annotations\Proxy(false)
 */
class Flow extends \TYPO3\Surf\Application\BaseApplication {

	/**
	 * Constructor
	 */
	public function __construct($name = 'TYPO3 Flow') {
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
			->addTask('typo3.surf:typo3:flow:createdirectories', 'initialize', $this)
			->afterTask('typo3.surf:gitcheckout', array(
				'typo3.surf:composer:install',
				'typo3.surf:typo3:flow:symlinkdata',
				'typo3.surf:typo3:flow:symlinkconfiguration',
				'typo3.surf:typo3:flow:copyconfiguration',
				'typo3.surf:typo3:flow:setfilepermissions'
			), $this)
			->addTask('typo3.surf:typo3:flow:migrate', 'migrate', $this);
	}

}
?>