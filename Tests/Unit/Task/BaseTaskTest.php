<?php
namespace TYPO3\Surf\Tests\Unit\Task;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;

/**
 * Base unit test for tasks
 */
abstract class BaseTaskTest extends \PHPUnit_Framework_TestCase
{
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
     * @var \TYPO3\Surf\Domain\Model\Task
     */
    protected $task;

    /**
     * @var \TYPO3\Surf\Domain\Model\Node
     */
    protected $node;

    /**
     * @var \TYPO3\Surf\Domain\Model\Application
     */
    protected $application;

    /**
     * @var \TYPO3\Surf\Domain\Model\Deployment
     */
    protected $deployment;

    /**
     * Set up test dependencies
     *
     * This sets up a stubbed shell command service to record command executions
     * and return predefined command responses.
     */
    public function setUp()
    {
        parent::setUp();

        $this->commands = array('executed' => array());
        $commands = &$this->commands;
        $this->responses = array();
        $responses = &$this->responses;

        /** @var \TYPO3\Surf\Domain\Service\ShellCommandService|\PHPUnit_Framework_MockObject_MockObject $shellCommandService */
        $shellCommandService = $this->getMock('TYPO3\Surf\Domain\Service\ShellCommandService');
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
        $shellCommandService->expects($this->any())->method('executeOrSimulate')->will($this->returnCallback(function ($command) use (&$commands, $responses) {
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

        $this->node = new \TYPO3\Surf\Domain\Model\Node('TestNode');
        $this->deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $mockLogger = $this->getMock('Psr\Log\LoggerInterface');
        $this->deployment->setLogger($mockLogger);
        $this->deployment->setWorkspacesBasePath('./Data/Surf');
        $this->application = new \TYPO3\Surf\Domain\Model\Application('TestApplication');

        $this->deployment->initialize();
    }

    /**
     * Assert that a command was executed
     *
     * The substring will be matched against all executed commands
     * (called with execute or executeOrSimulate).
     *
     * @param string $commandSubstring A command substring that was expected to be executed or a PREG pattern (e.g. "/git init .* -q/")
     * @return void
     */
    protected function assertCommandExecuted($commandSubstring)
    {
        $this->assertThat($this->commands['executed'], new \TYPO3\Surf\Tests\Unit\AssertCommandExecuted($commandSubstring));
    }

    /**
     * @return \TYPO3\Surf\Domain\Model\Task
     */
    abstract protected function createTask();
}
