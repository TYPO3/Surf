<?php
namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A task manager
 *
 */
class TaskManager
{
    /**
     * Task history for rollback
     * @var array
     */
    protected $taskHistory = array();

    /**
     * Execute a task
     *
     * @param string $taskName
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param string $stage
     * @param array $options Local task options
     * @param string $definedTaskName
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function execute($taskName, Node $node, Application $application, Deployment $deployment, $stage, array $options = array(), $definedTaskName = '')
    {
        $definedTaskName = $definedTaskName ?: $taskName;
        $deployment->getLogger()->info($node->getName() . ' (' . $application->getName() . ') ' . $definedTaskName);

        $task = $this->createTaskInstance($taskName);

        $globalOptions = $this->overrideOptions($definedTaskName, $deployment, $node, $application, $options);

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
    public function rollback()
    {
        foreach (array_reverse($this->taskHistory) as $historicTask) {
            $historicTask['deployment']->getLogger()->info('Rolling back ' . get_class($historicTask['task']));
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
    public function reset()
    {
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
     * GitCheckoutTask could be expressed like 'TYPO3\\Surf\\Task\\GitCheckoutTask[sha1]' => '1234...'.
     *
     * @param string $taskName
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param array $taskOptions
     * @return array
     */
    protected function overrideOptions($taskName, Deployment $deployment, Node $node, Application $application, array $taskOptions)
    {
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
     * @return \TYPO3\Surf\Domain\Model\Task $task
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function createTaskInstance($taskName)
    {
        $taskClassName = $this->mapTaskNameToTaskClass($taskName);
        $task = new $taskClassName();
        if ($task instanceof ShellCommandServiceAwareInterface) {
            $task->setShellCommandService(new ShellCommandService());
        }
        return $task;
    }

    /**
     * Map the task name to the proper task class
     *
     * @param string $taskName
     * @return string $taskName
     * @throws \TYPO3\Surf\Exception
     */
    protected function mapTaskNameToTaskClass($taskName)
    {
        if (class_exists($taskName)) {
            return $taskName;
        }
        throw new \TYPO3\Surf\Exception(sprintf('No task found for identifier "%s". Make sure this is a valid class name or a defined task with valid base class name!', $taskName), 1451210811);
    }
}
