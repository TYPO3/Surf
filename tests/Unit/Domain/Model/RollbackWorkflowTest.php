<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TYPO3\Surf\Domain\Enum\RollbackWorkflowStage;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\RollbackWorkflow;
use TYPO3\Surf\Domain\Service\TaskManager;
use TYPO3\Surf\Exception as SurfException;
use TYPO3\Surf\Task\Generic\RollbackTask;
use TYPO3\Surf\Tests\Unit\KernelAwareTrait;

class RollbackWorkflowTest extends TestCase
{
    use KernelAwareTrait;

    /**
     * @test
     */
    public function deploymentMustBeInitializedBeforeRunning(): void
    {
        $this->expectException(SurfException::class);
        $deployment = $this->buildDeployment();
        $workflow = $deployment->getWorkflow();

        $workflow->run($deployment);
    }

    /**
     * @test
     */
    public function runFailsIfNoApplicationIsConfigured(): void
    {
        $this->expectException(SurfException::class);
        $deployment = $this->buildDeployment();
        $workflow = $deployment->getWorkflow();

        $deployment->initialize();

        try {
            $workflow->run($deployment);
        } catch (SurfException $exception) {
            self::assertSame(1334652420, $exception->getCode());
            throw $exception;
        }
    }

    /**
     * @test
     */
    public function runFailsIfNoNodesAreConfigured(): void
    {
        $this->expectException(SurfException::class);
        $deployment = $this->buildDeployment();
        $workflow = $deployment->getWorkflow();

        $deployment->addApplication(new Application('Test application'));

        $deployment->initialize();

        try {
            $workflow->run($deployment);
        } catch (SurfException $exception) {
            self::assertSame(1334652427, $exception->getCode());
            throw $exception;
        }
    }

    /**
     * Data provider with task definitions and expected executions
     *
     * Tests a simple setup with one node and one application.
     */
    public function globalTaskDefinitions(): \Iterator
    {
        yield [
            'Just one global task in stage initialize',
            static fn (RollbackWorkflow $workflow, Application $application): callable => static function () use ($workflow): void {
                $workflow
                    ->addTask('typo3.surf:test:setup', RollbackWorkflowStage::STEP_01_INITIALIZE);
            },
            [
                [
                    'task' => 'typo3.surf:test:setup',
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                    'options' => []
                ],
                [
                    'task' => RollbackTask::class,
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_02_EXECUTE,
                    'options' => []
                ]
            ]
        ];
        yield [
            'Add multiple tasks with afterTask',
            fn (RollbackWorkflow $workflow, Application $application): callable => static function () use ($workflow): void {
                $workflow
                    ->addTask('typo3.surf:test:setup', RollbackWorkflowStage::STEP_01_INITIALIZE)
                    ->afterTask('typo3.surf:test:setup', ['typo3.surf:test:secondsetup', 'typo3.surf:test:thirdsetup'])
                    ->afterTask('typo3.surf:test:secondsetup', 'typo3.surf:test:finalize');
            },
            [
                [
                    'task' => 'typo3.surf:test:setup',
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                    'options' => []
                ],
                [
                    'task' => 'typo3.surf:test:secondsetup',
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                    'options' => []
                ],
                [
                    'task' => 'typo3.surf:test:finalize',
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                    'options' => []
                ],
                [
                    'task' => 'typo3.surf:test:thirdsetup',
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                    'options' => []
                ],
                [
                    'task' => RollbackTask::class,
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_02_EXECUTE,
                    'options' => []
                ]
            ]
        ];
        yield [
            'Tasks in different stages',
            static fn (RollbackWorkflow $workflow, Application $application): callable => static function () use ($workflow): void {
                $workflow
                    ->addTask('typo3.surf:test:setup', RollbackWorkflowStage::STEP_01_INITIALIZE)
                    ->addTask('typo3.surf:test:checkout', RollbackWorkflowStage::STEP_02_EXECUTE)
                    ->addTask('typo3.surf:test:symlink', RollbackWorkflowStage::STEP_03_CLEANUP);
            },
            [
                [
                    'task' => 'typo3.surf:test:setup',
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                    'options' => []
                ],
                [
                    'task' => 'typo3.surf:test:checkout',
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_02_EXECUTE,
                    'options' => []
                ],
                [
                    'task' => RollbackTask::class,
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_02_EXECUTE,
                    'options' => []
                ],
                [
                    'task' => 'typo3.surf:test:symlink',
                    'node' => 'test1.example.com',
                    'application' => 'Test application',
                    'deployment' => 'Test rollback deployment',
                    'stage' => RollbackWorkflowStage::STEP_03_CLEANUP,
                    'options' => []
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider globalTaskDefinitions
     */
    public function globalTaskDefinitionsAreExecutedCorrectly(
        string $message,
        callable $initializeCallback,
        array $expectedExecutions
    ): void {
        $executedTasks = [];
        $deployment = $this->buildDeployment($executedTasks);
        $workflow = $deployment->getWorkflow();

        $application = new Application('Test application');
        $application->addNode(new Node('test1.example.com'));
        $deployment
            ->addApplication($application)
            ->onInitialize($initializeCallback($workflow, $application));

        $deployment->initialize();

        $workflow->run($deployment);

        self::assertEquals($expectedExecutions, $executedTasks, $message);
    }

    /**
     * @test
     */
    public function tasksAreExecutedInTheRightOrder(): void
    {
        $executedTasks = [];
        $deployment = $this->buildDeployment($executedTasks);
        $workflow = $deployment->getWorkflow();

        $flowApplication = new Application('Neos Flow Application');
        $flowApplication->addNode(new Node('flow-1.example.com'));

        $deployment->addApplication($flowApplication);

        $deployment->initialize();

        $workflow->addTask('task1:initialize', RollbackWorkflowStage::STEP_01_INITIALIZE);
        $workflow->addTask('task3:initialize', RollbackWorkflowStage::STEP_01_INITIALIZE);
        $workflow->afterTask('task1:initialize', 'task2:initialize');

        $workflow->beforeStage(RollbackWorkflowStage::STEP_01_INITIALIZE, 'before1:initialize');
        $workflow->beforeStage(RollbackWorkflowStage::STEP_01_INITIALIZE, 'before3:initialize');
        $workflow->afterTask('before1:initialize', 'before2:initialize');

        $workflow->afterStage(RollbackWorkflowStage::STEP_01_INITIALIZE, 'after1:initialize');
        $workflow->afterStage(RollbackWorkflowStage::STEP_01_INITIALIZE, 'after3:initialize');
        $workflow->afterTask('after1:initialize', 'after2:initialize');

        $workflow->addTask('task1:cleanup', RollbackWorkflowStage::STEP_03_CLEANUP);

        $workflow->run($deployment);

        $expected = [
            [
                'task' => 'before1:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                'options' => []
            ],
            [
                'task' => 'before2:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                'options' => []
            ],
            [
                'task' => 'before3:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                'options' => []
            ],
            [
                'task' => 'task1:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                'options' => []
            ],
            [
                'task' => 'task2:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                'options' => []
            ],
            [
                'task' => 'task3:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                'options' => []
            ],
            [
                'task' => 'after1:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                'options' => []
            ],
            [
                'task' => 'after2:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                'options' => []
            ],
            [
                'task' => 'after3:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_01_INITIALIZE,
                'options' => []
            ],
            [
                'task' => RollbackTask::class,
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_02_EXECUTE,
                'options' => []
            ],
            [
                'task' => 'task1:cleanup',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test rollback deployment',
                'stage' => RollbackWorkflowStage::STEP_03_CLEANUP,
                'options' => []
            ],
        ];

        self::assertEquals($expected, $executedTasks);
    }

    /**
     * Build a Deployment object with Workflow for testing
     *
     * @param array $executedTasks Register for executed tasks
     *
     * @return Deployment A configured Deployment for testing
     */
    protected function buildDeployment(array &$executedTasks = []): Deployment
    {
        $deployment = new Deployment(static::getKernel()->getContainer(), 'Test rollback deployment');
        $mockLogger = $this->createMock(LoggerInterface::class);
        // Enable log to console to debug tests
        // $mockLogger->expects(self::any())->method('log')->will($this->returnCallback(function($message) {
        //   echo $message . chr(10);
        // }));
        $deployment->setLogger($mockLogger);

        $mockTaskManager = $this->createMock(TaskManager::class);
        $mockTaskManager
            ->expects(self::any())
            ->method('execute')
            ->willReturnCallback(
                function (
                    $task,
                    Node $node,
                    Application $application,
                    Deployment $deployment,
                    $stage,
                    array $options = []
                ) use (&$executedTasks): void {
                    $executedTasks[] = [
                        'task' => $task,
                        'node' => $node->getName(),
                        'application' => $application->getName(),
                        'deployment' => $deployment->getName(),
                        'stage' => $stage,
                        'options' => $options
                    ];
                }
            );

        $workflow = new RollbackWorkflow($mockTaskManager);
        $workflow->setLogger($mockLogger);
        $deployment->setWorkflow($workflow);

        return $deployment;
    }
}
