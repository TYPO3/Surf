<?php
namespace TYPO3\Surf\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * A Workflow
 *
 */
abstract class Workflow {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\TaskManager
	 */
	protected $taskManager;

	/**
	 * @var array
	 */
	protected $tasks = array();

	/**
	 *
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return void
	 * @throws \TYPO3\Surf\Exception
	 */
	public function run(Deployment $deployment) {
		if (!$deployment->isInitialized()) {
			throw new \TYPO3\Surf\Exception('Deployment must be initialized before running it', 1335976529);
		}
		$deployment->getLogger()->log('Using workflow "' . $this->getName() . '"', LOG_DEBUG);
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
			foreach ($this->tasks['stage'] as $applicationName => $steps) {
				foreach ($steps as $step => $tasksByStageStep) {
					foreach ($tasksByStageStep as $stageName => $tasks) {
						$this->tasks['stage'][$applicationName][$stageName][$step] = array_filter($tasks, function($task) use ($removeTask) { return $task !== $removeTask; });
					}
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
	 * @param string|array $tasks
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function forStage($stage, $tasks) {
		return $this->addTask($tasks, $stage);
	}

	/**
	 * Add the given tasks to a step in a stage and optionally a specific application
	 *
	 * The tasks will be executed for the given stage. If an application is given,
	 * the tasks will be executed only for the stage and application.
	 *
	 * @param string|array $tasks
	 * @param string $stage The name of the stage when this task shall be executed
	 * @param \TYPO3\Surf\Domain\Model\Application $application If given the task will be specific for this application
	 * @param string $step A stage has three steps "before", "tasks" and "after"
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	protected function addTaskToStage($tasks, $stage, Application $application = NULL, $step = 'tasks') {
		if (!is_array($tasks)) {
			$tasks = array($tasks);
		}

		$applicationName = $application !== NULL ? $application->getName() : '_';

		if (!isset($this->tasks['stage'][$applicationName][$stage][$step])) {
			$this->tasks['stage'][$applicationName][$stage][$step] = array();
		}

		$this->tasks['stage'][$applicationName][$stage][$step] = array_merge($this->tasks['stage'][$applicationName][$stage][$step], $tasks);
	}

	/**
	 * Add the given tasks for a stage and optionally a specific application
	 *
	 * The tasks will be executed for the given stage. If an application is given,
	 * the tasks will be executed only for the stage and application.
	 *
	 * @param string|array $tasks
	 * @param string $stage The name of the stage when this task shall be executed
	 * @param \TYPO3\Surf\Domain\Model\Application $application If given the task will be specific for this application
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function addTask($tasks, $stage, Application $application = NULL) {
		$this->addTaskToStage($tasks, $stage, $application);
		return $this;
	}

	/**
	 * Add tasks that shall be executed after the given task
	 *
	 * The execution will not depend on a stage but on an optional application.
	 *
	 * @param string $task
	 * @param string|array $tasks
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
	 * @param string|array $tasks
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
	 * Add tasks that shall be executed before the given stage
	 *
	 * @param string $stage
	 * @param string|array $tasks
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function beforeStage($stage, $tasks, Application $application = NULL) {
		$this->addTaskToStage($tasks, $stage, $application, 'before');
		return $this;
	}

	/**
	 * Add tasks that shall be executed after the given stage
	 *
	 * @param string $stage
	 * @param string|array $tasks
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function afterStage($stage, $tasks, Application $application = NULL) {
		$this->addTaskToStage($tasks, $stage, $application, 'after');
		return $this;
	}

	/**
	 * Override options for given task
	 *
	 * @param string $taskName
	 * @param array $options
	 * @return \TYPO3\Surf\Domain\Model\Workflow
	 */
	public function setTaskOptions($taskName, $options) {
		$baseTask = $taskName;
		if (isset($this->tasks['defined'][$taskName]) && is_array($this->tasks['defined'][$taskName])) {
			$definedTask = $this->tasks['defined'][$taskName];
			$baseTask = $definedTask['task'];
			if (is_array($definedTask['options'])) {
				$options = array_merge_recursive($definedTask['options'], $options);
			}
		}
		$this->defineTask($taskName, $baseTask, $options);
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

			foreach (array('before', 'tasks', 'after') as $stageStep) {
				if (isset($this->tasks['stage'][$applicationName][$stage][$stageStep])) {
					$deployment->getLogger()->log('Executing stage "' . $stage . '" (step "' . $stageStep . '") on "' . $node->getName() . '" ' . $label, LOG_DEBUG);
					foreach ($this->tasks['stage'][$applicationName][$stage][$stageStep] as $task) {
						$this->executeTask($task, $node, $application, $deployment, $stage);
					}
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
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
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
			throw new \TYPO3\Surf\Exception\TaskExecutionException('Cycle for task "' . $task . '" detected, aborting.', 1335976544);
		}
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