<?php

declare(strict_types=1);

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
use UnexpectedValueException;

/**
 * @final
 */
class TaskFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function createTaskInstance(string $taskName): Task
    {
        return $this->createTask($taskName);
    }

    private function createTask(string $taskName): Task
    {
        if (! $this->container->has($taskName)) {
            $task = new $taskName();
            if ($task instanceof ShellCommandServiceAwareInterface) {
                $task->setShellCommandService(new ShellCommandService());
            }
        } else {
            $task = $this->container->get($taskName);
        }

        if (!$task instanceof Task) {
            throw new UnexpectedValueException('Variable $task is not of type Task');
        }

        return $task;
    }
}
