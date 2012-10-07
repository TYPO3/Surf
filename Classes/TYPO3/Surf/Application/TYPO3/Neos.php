<?php
namespace TYPO3\Surf\Application\TYPO3;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A TYPO3 Neos application template
 *
 */
class Neos extends \TYPO3\Surf\Application\TYPO3\Flow {

	/**
	 * Constructor
	 */
	public function __construct($name = 'TYPO3 Neos') {
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

		$workflow->addTask('typo3.surf:typo3:neos:importsite', 'migrate', $this);
	}

}
?>