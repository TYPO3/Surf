<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Model;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use TYPO3\Surf\Domain\Service\TaskManager;
use TYPO3\Surf\Exception as SurfException;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * A Workflow
 */
abstract class Workflow implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    protected TaskManager $taskManager;

    protected array $tasks = [];

    public function __construct(TaskManager $taskManager)
    {
        $this->taskManager = $taskManager;
    }

    public function run(Deployment $deployment): void
    {
        if (!$deployment->isInitialized()) {
            throw new SurfException('Deployment must be initialized before running it', 1335976529);
        }
        $this->logger->debug('Using workflow "' . $this->getName() . '"');
    }

    abstract public function getName(): string;

    /**
     * Remove the given task from all stages and applications
     */
    public function removeTask(string $removeTask, Application $application = null): self
    {
        $removeApplicationName = $application instanceof Application ? $application->getName() : null;

        $applicationRemovalGuardClause = fn ($applicationName): bool => null !== $removeApplicationName && $applicationName !== $removeApplicationName;

        if (isset($this->tasks['stage'])) {
            foreach ($this->tasks['stage'] as $applicationName => $steps) {
                if ($applicationRemovalGuardClause($applicationName)) {
                    continue;
                }
                foreach ($steps as $step => $tasksByStageStep) {
                    foreach ($tasksByStageStep as $stageName => $tasks) {
                        $this->tasks['stage'][$applicationName][$step][$stageName] = array_filter($tasks, fn ($task): bool => $task !== $removeTask);
                    }
                }
            }
        }
        if (isset($this->tasks['before'])) {
            foreach ($this->tasks['before'] as $applicationName => $tasksByTask) {
                if ($applicationRemovalGuardClause($applicationName)) {
                    continue;
                }
                foreach ($tasksByTask as $taskName => $tasks) {
                    $this->tasks['before'][$applicationName][$taskName] = array_filter($tasks, fn ($task): bool => $task !== $removeTask);
                }
            }
        }
        if (isset($this->tasks['after'])) {
            foreach ($this->tasks['after'] as $applicationName => $tasksByTask) {
                if ($applicationRemovalGuardClause($applicationName)) {
                    continue;
                }
                foreach ($tasksByTask as $taskName => $tasks) {
                    $this->tasks['after'][$applicationName][$taskName] = array_filter($tasks, fn ($task): bool => $task !== $removeTask);
                }
            }
        }

        return $this;
    }

    /**
     * @param array|string $tasks
     */
    public function forStage(string $stage, $tasks): self
    {
        return $this->addTask($tasks, $stage);
    }

    /**
     * Add the given tasks to a step in a stage and optionally a specific application
     *
     * The tasks will be executed for the given stage. If an application is given,
     * the tasks will be executed only for the stage and application.
     *
     * @param array|string $tasks
     * @param string $stage The name of the stage when this task shall be executed
     * @param string $step A stage has three steps "before", "tasks" and "after"
     */
    protected function addTaskToStage($tasks, string $stage, Application $application = null, string $step = 'tasks'): void
    {
        if (!is_array($tasks)) {
            $tasks = [$tasks];
        }

        $applicationName = $application !== null ? $application->getName() : '_';

        if (!isset($this->tasks['stage'][$applicationName][$stage][$step])) {
            $this->tasks['stage'][$applicationName][$stage][$step] = [];
        }

        $this->tasks['stage'][$applicationName][$stage][$step] = array_merge($this->tasks['stage'][$applicationName][$stage][$step], $tasks);
    }

    /**
     * Add the given tasks for a stage and optionally a specific application
     *
     * The tasks will be executed for the given stage. If an application is given,
     * the tasks will be executed only for the stage and application.
     *
     * @param array|string $tasks
     * @param string $stage The name of the stage when this task shall be executed
     *
     * @return Workflow
     */
    public function addTask($tasks, string $stage, Application $application = null)
    {
        $this->addTaskToStage($tasks, $stage, $application);

        return $this;
    }

    /**
     * Add tasks that shall be executed after the given task
     *
     * The execution will not depend on a stage but on an optional application.
     *
     * @param array|string $tasks
     */
    public function afterTask(string $task, $tasks, Application $application = null): self
    {
        if (!is_array($tasks)) {
            $tasks = [$tasks];
        }

        $applicationName = $application !== null ? $application->getName() : '_';

        if (!isset($this->tasks['after'][$applicationName][$task])) {
            $this->tasks['after'][$applicationName][$task] = [];
        }
        $this->tasks['after'][$applicationName][$task] = array_merge($this->tasks['after'][$applicationName][$task], $tasks);

        return $this;
    }

    /**
     * Add tasks that shall be executed before the given task
     *
     * The execution will not depend on a stage but on an optional application.
     *
     * @param array|string $tasks
     */
    public function beforeTask(string $task, $tasks, Application $application = null): self
    {
        if (!is_array($tasks)) {
            $tasks = [$tasks];
        }

        $applicationName = $application !== null ? $application->getName() : '_';

        if (!isset($this->tasks['before'][$applicationName][$task])) {
            $this->tasks['before'][$applicationName][$task] = [];
        }
        $this->tasks['before'][$applicationName][$task] = array_merge($this->tasks['before'][$applicationName][$task], $tasks);

        return $this;
    }

    /**
     * Define a new task based on an existing task by setting options
     */
    public function defineTask(string $taskName, string $baseTask, array $options): self
    {
        $this->tasks['defined'][$taskName] = [
            'task' => $baseTask,
            'options' => $options
        ];
        return $this;
    }

    /**
     * Add tasks that shall be executed before the given stage
     *
     * @param array|string $tasks
     */
    public function beforeStage(string $stage, $tasks, Application $application = null): self
    {
        $this->addTaskToStage($tasks, $stage, $application, 'before');

        return $this;
    }

    /**
     * Add tasks that shall be executed after the given stage
     *
     * @param array|string $tasks
     */
    public function afterStage(string $stage, $tasks, Application $application = null): self
    {
        $this->addTaskToStage($tasks, $stage, $application, 'after');

        return $this;
    }

    /**
     * Override options for given task
     */
    public function setTaskOptions(string $taskName, array $options): self
    {
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
     * Returns list of all registered tasks
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * Execute a stage for a node and application
     */
    protected function executeStage(string $stage, Node $node, Application $application, Deployment $deployment): void
    {
        foreach (['before', 'tasks', 'after'] as $stageStep) {
            foreach (['_', $application->getName()] as $applicationName) {
                $label = $applicationName === '_' ? 'for all' : 'for application ' . $applicationName;

                if (isset($this->tasks['stage'][$applicationName][$stage][$stageStep])) {
                    $this->logger->debug('Executing stage "' . $stage . '" (step "' . $stageStep . '") on "' . $node->getName() . '" ' . $label);
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
     */
    protected function executeTask(string $task, Node $node, Application $application, Deployment $deployment, string $stage, array &$callstack = []): void
    {
        foreach (['_', $application->getName()] as $applicationName) {
            if (isset($this->tasks['before'][$applicationName][$task])) {
                foreach ($this->tasks['before'][$applicationName][$task] as $beforeTask) {
                    $this->logger->debug('Task "' . $beforeTask . '" before "' . $task);
                    $this->executeTask($beforeTask, $node, $application, $deployment, $stage, $callstack);
                }
            }
        }
        if (isset($callstack[$task])) {
            throw new TaskExecutionException('Cycle for task "' . $task . '" detected, aborting.', 1335976544);
        }
        if (isset($this->tasks['defined'][$task])) {
            $this->taskManager->execute($this->tasks['defined'][$task]['task'], $node, $application, $deployment, $stage, $this->tasks['defined'][$task]['options'], $task);
        } else {
            $this->taskManager->execute($task, $node, $application, $deployment, $stage);
        }
        $callstack[$task] = true;
        foreach (['_', $application->getName()] as $applicationName) {
            $label = $applicationName === '_' ? 'for all' : 'for application ' . $applicationName;
            if (isset($this->tasks['after'][$applicationName][$task])) {
                foreach ($this->tasks['after'][$applicationName][$task] as $beforeTask) {
                    $this->logger->debug('Task "' . $beforeTask . '" after "' . $task . '" ' . $label);
                    $this->executeTask($beforeTask, $node, $application, $deployment, $stage, $callstack);
                }
            }
        }
    }
}
