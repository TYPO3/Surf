<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Model;

/**
 * @codeCoverageIgnore
 */
final class TaskInHistory
{
    private Task $task;

    private Node $node;

    private Application $application;

    private Deployment $deployment;

    private string $stage;

    private array $options;

    private function __construct(Task $task, Node $node, Application $application, Deployment $deployment, string $stage, array $options)
    {
        $this->task = $task;
        $this->node = $node;
        $this->application = $application;
        $this->deployment = $deployment;
        $this->stage = $stage;
        $this->options = $options;
    }

    public static function create(Task $task, Node $node, Application $application, Deployment $deployment, string $stage, array $options): TaskInHistory
    {
        return new self($task, $node, $application, $deployment, $stage, $options);
    }

    public function task(): Task
    {
        return $this->task;
    }

    public function node(): Node
    {
        return $this->node;
    }

    public function application(): Application
    {
        return $this->application;
    }

    public function deployment(): Deployment
    {
        return $this->deployment;
    }

    public function stage(): string
    {
        return $this->stage;
    }

    public function options(): array
    {
        return $this->options;
    }
}
