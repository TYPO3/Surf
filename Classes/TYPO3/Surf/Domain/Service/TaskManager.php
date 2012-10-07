<?php
namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use \TYPO3\Surf\Domain\Model\Node;
use \TYPO3\Surf\Domain\Model\Application;
use \TYPO3\Surf\Domain\Model\Deployment;

/**
 * A task manager
 *
 */
class TaskManager {

	/**
	 * Task history for rollback
	 * @var array
	 */
	protected $taskHistory = array();

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Execute a task
	 *
	 * @param string $taskName
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param string $stage
	 * @param array $options Local task options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	public function execute($taskName, Node $node, Application $application, Deployment $deployment, $stage, array $options = array()) {
		$deployment->getLogger()->log($node->getName() . ' (' . $application->getName() . ') ' . $taskName, LOG_INFO);

		$task = $this->createTaskInstance($taskName);

		$globalOptions = $this->overrideOptions($taskName, $deployment, $node, $application, $options);

		if (!$deployment->isDryRun()) {
			$task->execute($node, $application, $deployment, $globalOptions);
		} else {
			$task->simulate($node, $application, $deployment, $globalOptions);
		}
		$this->taskHistory[] = array(
			'task' => $task,
			'node' => $node,
			'application' => $application,
			'deployment' => $deployment,
			'stage' => $stage,
			'options' => $globalOptions
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

	/**
	 * Override options for a task
	 *
	 * The order of the options is:
	 *
	 *   Deployment, Node, Application, Task
	 *
	 * A task option will always override more global options from the
	 * Deployment, Node or Application.
	 *
	 * Global options for a task should be prefixed with the task name to prevent naming
	 * issues between different tasks. For example passing a special option to the
	 * GitCheckoutTask could be expressed like 'typo3.surf:gitcheckout[sha1]' => '1234...'.
	 *
	 * @param string $taskName
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param array $taskOptions
	 * @return array
	 */
	protected function overrideOptions($taskName, Deployment $deployment, Node $node, Application $application, array $taskOptions) {
		$globalOptions = array_merge(
			$deployment->getOptions(),
			$node->getOptions(),
			$application->getOptions()
		);
		$globalTaskOptions = array();
		foreach ($globalOptions as $optionKey => $optionValue) {
			if (strlen($optionKey) > strlen($taskName) && strpos($optionKey, $taskName) === 0 && $optionKey[strlen($taskName)] === '[') {
				$globalTaskOptions[substr($optionKey, strlen($taskName) + 1, -1)] = $optionValue;
			}
		}

		return array_merge(
			$globalOptions,
			$globalTaskOptions,
			$taskOptions
		);
	}

	/**
	 * Create a task instance from the given task name
	 *
	 * @param string $taskName
	 * @return \TYPO3\Surf\Domain\Model\Task
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	protected function createTaskInstance($taskName) {
		list($packageKey, $taskName) = explode(':', $taskName, 2);
		$taskClassName = strtr($packageKey, '.', '\\') . '\\Task\\' . strtr($taskName, ':', '\\') . 'Task';
		$taskObjectName = $this->objectManager->getCaseSensitiveObjectName($taskClassName);
		if (!$this->objectManager->isRegistered($taskObjectName)) {
			throw new \TYPO3\Surf\Exception\InvalidConfigurationException('Task "' . $taskName . '" was not registered ' . $taskClassName, 1335976651);
		}
		$task = new $taskObjectName();
		return $task;
	}

}
?>