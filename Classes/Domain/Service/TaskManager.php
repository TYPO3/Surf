<?php
namespace TYPO3\Deploy\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

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
	 * @inject
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Execute a task
	 *
	 * @param string $task
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function execute($task, \TYPO3\Deploy\Domain\Model\Node $node, \TYPO3\Deploy\Domain\Model\Application $application, \TYPO3\Deploy\Domain\Model\Deployment $deployment) {
		list($packageKey, $taskName) = explode(':', $task, 2);
		$taskClassName = strtr($packageKey, '.', '\\') . '\\Task\\' . strtr($taskName, ':', '\\') . 'Task';
		$taskObjectName = $this->objectManager->getCaseSensitiveObjectName($taskClassName);
		if (!$this->objectManager->isRegistered($taskObjectName)) {
			throw new \Exception('Task "' . $task .  '" not registered ' . $taskClassName);
		}
		$task = $this->objectManager->create($taskObjectName);
		if (!$deployment->isDryRun()) {
			$task->execute($node, $application, $deployment);
		}
		$this->taskHistory[] = array(
			'task' => $task,
			'node' => $node,
			'application' => $application,
			'deployment' => $deployment
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
				$historicTask['task']->rollback($historicTask['node'], $historicTask['application'], $historicTask['deployment']);
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