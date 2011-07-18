<?php
namespace TYPO3\Deploy\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Deployment;

/**
 * A Workflow
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class Workflow {

	/**
	 * @inject
	 * @var \TYPO3\Deploy\Domain\Service\TaskManager
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
		$deployment->getLogger()->log('Using workflow "' . $this->getName() . '"');
	}

	/**
	 * Get a name for this type of workflow
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 *
	 * @param string $stage
	 * @param mixed $tasks
	 */
	public function forStage($stage, $tasks) {
		if (!is_array($tasks)) $tasks = array($tasks);
		if (!isset($this->tasks['stage']['_'][$stage])) $this->tasks['stage']['_'][$stage] = array();
		$this->tasks['stage']['_'][$stage] = array_merge($this->tasks['stage']['_'][$stage], $tasks);
		return $this;
	}

	/**
	 * Remove the given task from all stages and applications
	 *
	 * @param string $task
	 * @return void
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
			foreach ($this->tasks['after'] as $taskName => $tasks) {
				$this->tasks['after'][$taskName] = array_filter($tasks, function($task) use ($removeTask) { return $task !== $removeTask; });
			}
		}
		if (isset($this->tasks['before'])) {
			foreach ($this->tasks['before'] as $taskName => $tasks) {
				$this->tasks['before'][$taskName] = array_filter($tasks, function($task) use ($removeTask) { return $task !== $removeTask; });
			}
		}
	}

	/**
	 *
	 * @param string $stage
	 * @param string $application
	 * @param mixed $tasks
	 */
	public function forApplication($application, $stage, $tasks) {
		if (!is_array($tasks)) $tasks = array($tasks);
		if (!isset($this->tasks['stage'][$application->getName()][$stage])) $this->tasks['stage'][$application->getName()][$stage] = array();
		$this->tasks['stage'][$application->getName()][$stage] = array_merge($this->tasks['stage'][$application->getName()][$stage], $tasks);
		return $this;
	}

	/**
	 *
	 * @param string $task
	 * @param mixed $tasks
	 */
	public function afterTask($task, $tasks) {
		if (!is_array($tasks)) $tasks = array($tasks);
		if (!isset($this->tasks['after'][$task])) $this->tasks['after'][$task] = array();
		$this->tasks['after'][$task] = array_merge($this->tasks['after'][$task], $tasks);
		return $this;
	}

	/**
	 *
	 * @param string $task
	 * @param mixed $tasks
	 */
	public function beforeTask($task, $tasks) {
		if (!is_array($tasks)) $tasks = array($tasks);
		if (!isset($this->tasks['before'][$task])) $this->tasks['before'][$task] = array();
		$this->tasks['before'][$task] = array_merge($this->tasks['before'][$task], $tasks);
		return $this;
	}

	/**
	 *
	 * @param string $stage
	 * @param Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 */
	protected function executeStage($stage, $node, $application, $deployment) {
		if (isset($this->tasks['stage']['_'][$stage])) {
			$deployment->getLogger()->log('Executing stage "' . $stage . '" on "' . $node->getName() . '" for all', LOG_DEBUG);
			foreach ($this->tasks['stage']['_'][$stage] as $task) {
				$this->executeTask($task, $node, $application, $deployment);
			}
		}
		if (isset($this->tasks['stage'][$application->getName()][$stage])) {
			$deployment->getLogger()->log('Executing stage "' . $stage . '" on "' . $node->getName() . '" for application "' . $application->getName() . '"', LOG_DEBUG);
			foreach ($this->tasks['stage'][$application->getName()][$stage] as $task) {
				$this->executeTask($task, $node, $application, $deployment);
			}
		}
	}

	/**
	 *
	 * @param string $task
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @return void
	 */
	protected function executeTask($task, $node, $application, $deployment) {
		if (isset($this->tasks['before'][$task])) {
			foreach ($this->tasks['before'][$task] as $beforeTask) {
				$deployment->getLogger()->log('Task "' . $beforeTask . '" before "' . $task, LOG_DEBUG);
				$this->executeTask($beforeTask, $node, $application, $deployment);
			}
		}
		$deployment->getLogger()->log('Execute task "' . $task . '" on "' . $node->getName() . '" for application "' . $application->getName(), LOG_DEBUG);
		$this->taskManager->execute($task, array(
			'node' => $node,
			'application' => $application,
			'deployment' => $deployment
		));
		if (isset($this->tasks['after'][$task])) {
			foreach ($this->tasks['after'][$task] as $beforeTask) {
				$deployment->getLogger()->log('Task "' . $beforeTask . '" after "' . $task, LOG_DEBUG);
				$this->executeTask($beforeTask, $node, $application, $deployment);
			}
		}
	}

}
?>