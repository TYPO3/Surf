<?php
namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A task manager
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TaskManager {

	/**
	 * Task history for rollback
	 * @var array
	 */
	protected $taskHistory = array();

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Execute a task
	 *
	 * @param string $task
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute($task, \TYPO3\Surf\Domain\Model\Node $node, \TYPO3\Surf\Domain\Model\Application $application, \TYPO3\Surf\Domain\Model\Deployment $deployment, array $options = array()) {
		list($packageKey, $taskName) = explode(':', $task, 2);
		$taskClassName = strtr($packageKey, '.', '\\') . '\\Task\\' . strtr($taskName, ':', '\\') . 'Task';
		$taskObjectName = $this->objectManager->getCaseSensitiveObjectName($taskClassName);
		if (!$this->objectManager->isRegistered($taskObjectName)) {
			throw new \Exception('Task "' . $task .  '" not registered ' . $taskClassName);
		}
		$task = $this->objectManager->create($taskObjectName);
		if (!$deployment->isDryRun()) {
			$task->execute($node, $application, $deployment, $options);
		} else {
			$task->simulate($node, $application, $deployment, $options);
		}
		$this->taskHistory[] = array(
			'task' => $task,
			'node' => $node,
			'application' => $application,
			'deployment' => $deployment,
			'options' => $options
		);
	}

	/**
	 * Rollback all tasks stored in the task history in reverse order
	 *
	 * @return void
	 */
	public function rollback() {
		foreach (array_reverse($this->taskHistory) as $historicTask) {
			$historicTask['deployment']->getLogger()->log('Rolling back ' . get_class($historicTask['task']));
			if (!$historicTask['deployment']->isDryRun()) {
				$historicTask['task']->rollback($historicTask['node'], $historicTask['application'], $historicTask['deployment'], $historicTask['options']);
			}
		}
		$this->reset();
	}

	/**
	 * Reset the task history
	 *
	 * @return void
	 */
	public function reset() {
		$this->taskHistory = array();
	}

}
?>