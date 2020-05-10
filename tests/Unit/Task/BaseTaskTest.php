<?php
namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
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
    use KernelAwareTrait;

    /**
     * Executed commands
     * @var array
     */
    protected $commands;

    /**
     * Predefined command respones
     * @var array
     */
    protected $responses;

    /**
     * @var Task
     */
    protected $task;

    /**
     * @var Node
     */
    protected $node;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Deployment
     */
    protected $deployment;

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
    protected function setUp()
    {
        $this->commands = ['executed' => []];
        $commands = &$this->commands;
        $this->responses = [];
        $responses = &$this->responses;

        /** @var PHPUnit_Framework_MockObject_MockObject|ShellCommandService $shellCommandService */
        $shellCommandService = $this->createMock(ShellCommandService::class);
        $shellCommandService->expects($this->any())->method('execute')->will($this->returnCallback(function ($command) use (&$commands, &$responses) {
            if (is_array($command)) {
                $commands['executed'] = array_merge($commands['executed'], $command);
            } else {
                $commands['executed'][] = $command;
                if (isset($responses[$command])) {
                    return $responses[$command];
                }
            }
            return '';
        }));
        $shellCommandService->expects($this->any())->method('executeOrSimulate')->will($this->returnCallback(function ($command) use (&$commands, &$responses) {
            if (is_array($command)) {
                $commands['executed'] = array_merge($commands['executed'], $command);
            } else {
                $commands['executed'][] = $command;
                if (isset($responses[$command])) {
                    return $responses[$command];
                }
            }
            return '';
        }));
        $this->task = $this->createTask();
        if ($this->task instanceof ShellCommandServiceAwareInterface) {
            $this->task->setShellCommandService($shellCommandService);
        }

        $this->node = new Node('TestNode');
        $this->node->setHostname('hostname');
        $this->deployment = new Deployment('TestDeployment');
        $this->deployment->setContainer(static::getKernel()->getContainer());
        $this->mockLogger = $this->prophesize(LoggerInterface::class);
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
    protected function assertCommandExecuted($commandSubstring)
    {
        $this->assertThat($this->commands['executed'], new AssertCommandExecuted($commandSubstring));
    }

    /**
     * @return Task
     */
    abstract protected function createTask();
}
