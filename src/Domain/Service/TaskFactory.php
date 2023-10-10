<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Service;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
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
        if ($this->container === null || ! $this->container->has($taskName)) {
            $task = new $taskName();
            if ($task instanceof ShellCommandServiceAwareInterface) {
                $task->setShellCommandService(new ShellCommandService());
            }
            if ($this->container !== null && $task instanceof LoggerAwareInterface) {
                /** @var LoggerInterface $logger */
                $logger = $this->container->get(LoggerInterface::class);
                $task->setLogger($logger);
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
