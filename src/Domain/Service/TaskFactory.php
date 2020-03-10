<?php
declare(strict_types = 1);

namespace TYPO3\Surf\Domain\Service;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Exception as SurfException;

/**
 * @final
 */
class TaskFactory
{
    /**
     * Create a task instance from the given task name
     *
     * @return ShellCommandServiceAwareInterface|Task
     */
    public function createTaskInstance(string $taskName)
    {
        $taskClassName = $this->mapTaskNameToTaskClass($taskName);
        $task = new $taskClassName();

        if (!$task instanceof Task) {
            throw new SurfException(sprintf('The task %s is not a subclass of %s but of class %s', $taskName, Task::class, get_class($task)), 1451210811);
        }

        if ($task instanceof ShellCommandServiceAwareInterface) {
            $task->setShellCommandService(new ShellCommandService());
        }
        return $task;
    }

    private function mapTaskNameToTaskClass(string $taskName): string
    {
        if (!class_exists($taskName)) {
            throw new SurfException(sprintf('No task found for identifier "%s". Make sure this is a valid class name or a defined task with valid base class name!', $taskName), 1451210811);
        }

        return $taskName;
    }
}
