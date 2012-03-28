<?php
namespace TYPO3\Surf\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Workflow
 *
 */
abstract class Workflow {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Surf\Domain\Service\TaskManager
	 */
	protected $taskManager;

	/**
	 * @var array
	 */
	protected $tasks = array();

	/**
	 *
	 * @param Deployment $deployment
	 * @return void
	 */
	public function run(Deployment $deployment) {
		if (!$deployment->isInitialized()) {
			throw new \TYPO3\FLOW3\Exception('Deployment must be initialized before running it');
		}
		$deployment->getLogger()->log('Using workflow "' . $this->getName() . '"');
	}

	/**
	 * Get a name for this type of workflow
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 * Remove the given task from all stages and applications
	 *
	 * @param string $removeTask
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function removeTask($removeTask) {
		if (isset($this->tasks['stage'])) {
			foreach ($this->tasks['stage'] as $applicationName => $tasksByStage) {
				foreach ($tasksByStage as $stageName => $tasks) {
					$this->tasks['stage'][$applicationName][$stageName] = array_filter($tasks, function($task) use ($removeTask) { return $task !== $removeTask; });
				}
			}
		}
		if (isset($this->tasks['after'])) {
			foreach ($this->tasks['after'] as $applicationName => $tasksByTask) {
				foreach ($tasksByTask as $taskName => $tasks) {
					$this->tasks['after'][$applicationName][$taskName] = array_filter($tasks, function($task) use ($removeTask) { return $task !== $removeTask; });
				}
			}
		}
		if (isset($this->tasks['before'])) {
			foreach ($this->tasks['before'] as $applicationName => $tasksByTask) {
				foreach ($tasksByTask as $taskName => $tasks) {
					$this->tasks['before'][$applicationName][$taskName] = array_filter($tasks, function($task) use ($removeTask) { return $task !== $removeTask; });
				}
			}
		}
		return $this;
	}

	/**
	 *
	 * @param string $stage
	 * @param mixed $tasks
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function forStage($stage, $tasks) {
		return $this->addTask($tasks, $stage);
	}

	/**
	 * Add the given tasks for a stage and optionally a specific application
	 *
	 * The tasks will be executed for the given stage. If an application is given,
	 * the tasks will be executed only for the stage and application.
	 *
	 * @param mixed $tasks
	 * @param string $stage The name of the stage when this task shall be executed
	 * @param \TYPO3\Surf\Domain\Model\Application $application If given the task will be specific for this application
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function addTask($tasks, $stage, Application $application = NULL) {
		if (!is_array($tasks)) {
			$tasks = array($tasks);
		}

		$applicationName = $application !== NULL ? $application->getName() : '_';

		if (!isset($this->tasks['stage'][$applicationName][$stage])) {
			$this->tasks['stage'][$applicationName][$stage] = array();
		}
		$this->tasks['stage'][$applicationName][$stage] = array_merge($this->tasks['stage'][$applicationName][$stage], $tasks);
		return $this;
	}

	/**
	 * Add tasks that shall be executed after the given task
	 *
	 * The execution will not depend on a stage but on an optional application.
	 *
	 * @param string $task
	 * @param mixed $tasks
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function afterTask($task, $tasks, Application $application = NULL) {
		if (!is_array($tasks)) {
			$tasks = array($tasks);
		}

		$applicationName = $application !== NULL ? $application->getName() : '_';

		if (!isset($this->tasks['after'][$applicationName][$task])) {
			$this->tasks['after'][$applicationName][$task] = array();
		}
		$this->tasks['after'][$applicationName][$task] = array_merge($this->tasks['after'][$applicationName][$task], $tasks);
		return $this;
	}

	/**
	 * Add tasks that shall be executed before the given task
	 *
	 * The execution will not depend on a stage but on an optional application.
	 *
	 * @param string $task
	 * @param mixed $tasks
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function beforeTask($task, $tasks, Application $application = NULL) {
		if (!is_array($tasks)) {
			$tasks = array($tasks);
		}

		$applicationName = $application !== NULL ? $application->getName() : '_';

		if (!isset($this->tasks['before'][$applicationName][$task])) {
			$this->tasks['before'][$applicationName][$task] = array();
		}
		$this->tasks['before'][$applicationName][$task] = array_merge($this->tasks['before'][$applicationName][$task], $tasks);
		return $this;
	}

	/**
	 * Define a new task based on an existing task by setting options
	 *
	 * @param string $taskName
	 * @param string $baseTask
	 * @param array $options
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function defineTask($taskName, $baseTask, $options) {
		$this->tasks['defined'][$taskName] = array(
			'task' => $baseTask,
			'options' => $options
		);
		return $this;
	}

	/**
	 * Execute a stage for a node and application
	 *
	 * @param string $stage
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return void
	 */
	protected function executeStage($stage, Node $node, Application $application, Deployment $deployment) {
		foreach (array('_', $application->getName()) as $applicationName) {
			$label = $applicationName === '_' ? 'for all' : 'for application ' . $applicationName;
			if (isset($this->tasks['stage'][$applicationName][$stage])) {
				$deployment->getLogger()->log('Executing stage "' . $stage . '" on "' . $node->getName() . '" ' . $label, LOG_DEBUG);
				foreach ($this->tasks['stage'][$applicationName][$stage] as $task) {
					$this->executeTask($task, $node, $application, $deployment, $stage);
				}
			}
		}
	}

	/**
	 * Execute a task and consider configured before / after "hooks"
	 *
	 * Will also execute tasks that are registered to run before or after this task.
	 *
	 * @param string $task
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param string $stage
	 * @param array $callstack
	 * @return void
	 */
	protected function executeTask($task, Node $node, Application $application, Deployment $deployment, $stage, array &$callstack = array()) {
		foreach (array('_', $application->getName()) as $applicationName) {
			if (isset($this->tasks['before'][$applicationName][$task])) {
				foreach ($this->tasks['before'][$applicationName][$task] as $beforeTask) {
					$deployment->getLogger()->log('Task "' . $beforeTask . '" before "' . $task, LOG_DEBUG);
					$this->executeTask($beforeTask, $node, $application, $deployment, $stage, $callstack);
				}
			}
		}
		if (isset($callstack[$task])) {
			throw new \Exception('Cycle for task "' . $task . '" detected, aborting.');
		}
		$deployment->getLogger()->log('Execute task "' . $task . '" on "' . $node->getName() . '" for application "' . $application->getName() . '"', LOG_DEBUG);
		if (isset($this->tasks['defined'][$task])) {
			$this->taskManager->execute($this->tasks['defined'][$task]['task'], $node, $application, $deployment, $stage, $this->tasks['defined'][$task]['options']);
		} else {
			$this->taskManager->execute($task, $node, $application, $deployment, $stage);
		}
		$callstack[$task] = TRUE;
		foreach (array('_', $application->getName()) as $applicationName) {
			$label = $applicationName === '_' ? 'for all' : 'for application ' . $applicationName;
			if (isset($this->tasks['after'][$applicationName][$task])) {
				foreach ($this->tasks['after'][$applicationName][$task] as $beforeTask) {
					$deployment->getLogger()->log('Task "' . $beforeTask . '" after "' . $task . '" ' . $label, LOG_DEBUG);
					$this->executeTask($beforeTask, $node, $application, $deployment, $stage, $callstack);
				}
			}
		}
	}

}
?>