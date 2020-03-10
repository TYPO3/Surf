<?php
declare(strict_types = 1);

namespace TYPO3\Surf\Domain\Service;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Exception as SurfException;

/**
 * @final
 */
class TaskFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Create a task instance from the given task name
     *
     * @return ShellCommandServiceAwareInterface|Task
     */
    public function createTaskInstance(string $taskName)
    {
        $task = $this->container->get($taskName);

        if (!$task instanceof Task) {
            throw new SurfException(sprintf('The task %s is not a subclass of %s but of class %s', $taskName, Task::class, get_class($task)), 1451210811);
        }

        return $task;
    }
}
