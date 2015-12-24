<?php
namespace TYPO3\Surf\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * A task to create TYPO3 Flow specific directories
 *
 */
class CreateDirectoriesTask extends \TYPO3\Surf\Task\Generic\CreateDirectoriesTask {

	/**
	 * Execute this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$options = array(
			'directories' => array(
				'shared/Data/Logs',
				'shared/Data/Persistent',
				'shared/Configuration'
			)
		);
		parent::execute($node, $application, $deployment, $options);
	}
}
?>