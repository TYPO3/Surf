<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandService;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Tests\Unit\AssertCommandExecuted;
use TYPO3\Surf\Tests\Unit\KernelAwareTrait;

/**
 * Base unit test for tasks
 */
abstract class BaseTaskTest extends TestCase
{
    use ProphecyTrait;
    use KernelAwareTrait;

    /**
     * Executed commands
     */
    protected array $commands = [];

    /**
     * Predefined command responses
     */
    protected array $responses = [];

    protected Task $task;

    protected Node $node;

    protected Application $application;

    protected Deployment $deployment;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    protected $mockLogger;

    /**
     * Set up test dependencies
     *
     * This sets up a stubbed shell command service to record command executions
     * and return predefined command responses.
     */
    protected function setUp(): void
    {
        $this->commands = ['executed' => []];
        $commands = &$this->commands;
        $responses = &$this->responses;

        /** @var MockObject|ShellCommandService $shellCommandService */
        $shellCommandService = $this->createMock(ShellCommandService::class);
        $shellCommandService
            ->expects(self::any())
            ->method('execute')
            ->willReturnCallback(
                function ($command) use (&$commands, &$responses) {
                    if (is_array($command)) {
                        $commands['executed'] = array_merge($commands['executed'], $command);
                    } else {
                        $commands['executed'][] = $command;
                        if (isset($responses[$command])) {
                            return $responses[$command];
                        }
                    }
                    return '';
                }
            );
        $shellCommandService
            ->expects(self::any())
            ->method('executeOrSimulate')
            ->willReturnCallback(
                function ($command) use (&$commands, &$responses) {
                    if (is_array($command)) {
                        $commands['executed'] = array_merge($commands['executed'], $command);
                        foreach ($command as $singleCommand) {
                            if (isset($responses[$singleCommand])) {
                                return $responses[$singleCommand];
                            }
                        }
                    } else {
                        $commands['executed'][] = $command;
                        if (isset($responses[$command])) {
                            return $responses[$command];
                        }
                    }
                    return '';
                }
            );
        $this->task = $this->createTask();
        if ($this->task instanceof ShellCommandServiceAwareInterface) {
            $this->task->setShellCommandService($shellCommandService);
        }

        $this->node = new Node('TestNode');
        $this->node->setHostname('hostname');

        $this->deployment = new Deployment('TestDeployment');
        $this->deployment->setContainer(static::getKernel()->getContainer());

        $this->mockLogger = $this->prophesize(LoggerInterface::class);
        $this->task->setLogger($this->mockLogger->reveal());

        $this->deployment->setLogger($this->mockLogger->reveal());
        $this->deployment->setWorkspacesBasePath('./Data/Surf');

        $this->application = new Application('TestApplication');

        $this->deployment->initialize();
    }

    /**
     * Assert that a command was executed
     *
     * The substring will be matched against all executed commands
     * (called with execute or executeOrSimulate).
     *
     * @param string $commandSubstring A command substring that was expected to be executed or a PREG pattern (e.g. "/git init .* -q/")
     */
    protected function assertCommandExecuted(string $commandSubstring): void
    {
        self::assertThat($this->commands['executed'], new AssertCommandExecuted($commandSubstring));
    }

    /**
     * @return Task
     */
    abstract protected function createTask();
}
