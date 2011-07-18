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
	 * @var array
	 */
	protected $taskHistory = array();

	/**
	 * @inject
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 *
	 * @param string $task
	 * @param array $parameters
	 */
	public function execute($task, $parameters) {
		list($packageKey, $taskName) = explode(':', $task, 2);
		$taskClassName = strtr($packageKey, '.', '\\') . '\\Task\\' . strtr($taskName, ':', '\\') . 'Task';
		$taskObjectName = $this->objectManager->getCaseSensitiveObjectName($taskClassName);
		if (!$this->objectManager->isRegistered($taskObjectName)) {
			throw new \Exception('Task "' . $task .  '" not registered ' . $taskClassName);
		}
		$task = $this->objectManager->create($taskObjectName);
		$task->execute($parameters['node'], $parameters['application'], $parameters['deployment']);
		$this->taskHistory[] = array(
			'task' => $task,
			'parameters' => $parameters
		);
	}

	/**
	 * @return void
	 */
	public function rollback() {
		foreach (array_reverse($this->taskHistory) as $historicTask) {
			$historicTask['parameters']['deployment']->getLogger()->log('Rolling back ' . get_class($historicTask['task']));
			$historicTask['task']->rollback($historicTask['parameters']['node'], $historicTask['parameters']['application'], $historicTask['parameters']['deployment']);
		}
		$this->reset();
	}

	/**
	 * @return void
	 */
	public function reset() {
		$this->taskHistory = array();
	}

}
?>