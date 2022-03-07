<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Service;

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\TaskInHistory;

/**
 * @final
 */
class TaskManager
{
    /**
     * @var TaskInHistory[]
     */
    protected array $taskHistory = [];

    private TaskFactory $taskFactory;

    public function __construct(TaskFactory $taskFactory)
    {
        $this->taskFactory = $taskFactory;
    }

    public function execute(string $taskName, Node $node, Application $application, Deployment $deployment, string $stage, array $options = [], string $definedTaskName = ''): void
    {
        $definedTaskName = $definedTaskName ?: $taskName;
        $deployment->getLogger()->info($node->getName() . ' (' . $application->getName() . ') ' . $definedTaskName);

        $task = $this->taskFactory->createTaskInstance($taskName);

        $globalOptions = $this->overrideOptions($definedTaskName, $deployment, $node, $application, $options);

        $this->taskHistory[] = TaskInHistory::create($task, $node, $application, $deployment, $stage, $globalOptions);

        if (!$deployment->isDryRun()) {
            $task->execute($node, $application, $deployment, $globalOptions);
        } else {
            $task->simulate($node, $application, $deployment, $globalOptions);
        }
    }

    /**
     * Rollback all tasks stored in the task history in reverse order
     */
    public function rollback(): void
    {
        foreach (array_reverse($this->taskHistory) as $historicTask) {
            $historicTask->deployment()->getLogger()->info('Rolling back ' . get_class($historicTask->task()));
            if (!$historicTask->deployment()->isDryRun()) {
                $historicTask->task()->rollback($historicTask->node(), $historicTask->application(), $historicTask->deployment(), $historicTask->options());
            }
        }
        $this->reset();
    }

    public function reset(): void
    {
        $this->taskHistory = [];
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
     * GitCheckoutTask could be expressed like GitCheckoutTask::class . '[sha1]' => '1234...'.
     */
    protected function overrideOptions(string $taskName, Deployment $deployment, Node $node, Application $application, array $taskOptions): array
    {
        $globalOptions = array_merge(
            $deployment->getOptions(),
            $node->getOptions(),
            $application->getOptions()
        );
        $globalTaskOptions = [];
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
}
