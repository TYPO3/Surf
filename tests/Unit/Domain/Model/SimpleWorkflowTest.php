<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\SimpleWorkflow;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Service\TaskManager;
use TYPO3\Surf\Exception as SurfException;

/**
 * Unit test for SimpleWorkflow
 */
class SimpleWorkflowTest extends TestCase
{
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
     *
     * @return array
     */
    public function globalTaskDefinitions(): array
    {
        return [
            [
                'Just one global task in stage initialize',
                static function (Workflow $workflow, Application $application) {
                    return static function () use ($workflow): void {
                        $workflow
                            ->addTask('typo3.surf:test:setup', 'initialize');
                    };
                },
                [
                    [
                        'task' => 'typo3.surf:test:setup',
                        'node' => 'test1.example.com',
                        'application' => 'Test application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ]
                ]
            ],
            [
                'Add multiple tasks with afterTask',
                function (Workflow $workflow, Application $application) {
                    return static function () use ($workflow): void {
                        $workflow
                            ->addTask('typo3.surf:test:setup', 'initialize')
                            ->afterTask('typo3.surf:test:setup', ['typo3.surf:test:secondsetup', 'typo3.surf:test:thirdsetup'])
                            ->afterTask('typo3.surf:test:secondsetup', 'typo3.surf:test:finalize');
                    };
                },
                [
                    [
                        'task' => 'typo3.surf:test:setup',
                        'node' => 'test1.example.com',
                        'application' => 'Test application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ],
                    [
                        'task' => 'typo3.surf:test:secondsetup',
                        'node' => 'test1.example.com',
                        'application' => 'Test application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ],
                    [
                        'task' => 'typo3.surf:test:finalize',
                        'node' => 'test1.example.com',
                        'application' => 'Test application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ],
                    [
                        'task' => 'typo3.surf:test:thirdsetup',
                        'node' => 'test1.example.com',
                        'application' => 'Test application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ]
                ]
            ],
            [
                'Tasks in different stages',
                static function (Workflow $workflow, Application $application) {
                    return static function () use ($workflow): void {
                        $workflow
                            ->addTask('typo3.surf:test:setup', 'initialize')
                            ->addTask('typo3.surf:test:checkout', 'update')
                            ->addTask('typo3.surf:test:symlink', 'switch');
                    };
                },
                [
                    [
                        'task' => 'typo3.surf:test:setup',
                        'node' => 'test1.example.com',
                        'application' => 'Test application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ],
                    [
                        'task' => 'typo3.surf:test:checkout',
                        'node' => 'test1.example.com',
                        'application' => 'Test application',
                        'deployment' => 'Test deployment',
                        'stage' => 'update',
                        'options' => []
                    ],
                    [
                        'task' => 'typo3.surf:test:symlink',
                        'node' => 'test1.example.com',
                        'application' => 'Test application',
                        'deployment' => 'Test deployment',
                        'stage' => 'switch',
                        'options' => []
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider globalTaskDefinitions
     *
     * @param string $message
     * @param \Closure $initializeCallback
     * @param array $expectedExecutions
     */
    public function globalTaskDefinitionsAreExecutedCorrectly(
        $message,
        $initializeCallback,
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
     * Data provider with task definitions and expected executions
     *
     * A more complex setup with two applications running on three nodes.
     *
     * @return array
     */
    public function applicationTaskDefinitions(): array
    {
        return [
            [
                'Specific tasks for applications',
                function ($workflow, $applications) {
                    [$flowApplication, $typo3Application] = $applications;

                    return function () use ($workflow, $flowApplication, $typo3Application): void {
                        $workflow
                            ->addTask('typo3.surf:test:setup', 'initialize')
                            ->addTask('typo3.surf:test:doctrine:migrate', 'migrate', $flowApplication)
                            ->addTask('typo3.surf:test:em:updatedatabase', 'migrate', $typo3Application);
                    };
                },
                [
                    [
                        'task' => 'typo3.surf:test:setup',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ],
                    [
                        'task' => 'typo3.surf:test:setup',
                        'node' => 'flow-2.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ],
                    [
                        'task' => 'typo3.surf:test:setup',
                        'node' => 'neos.example.com',
                        'application' => 'TYPO3 Neos Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ],
                    [
                        'task' => 'typo3.surf:test:doctrine:migrate',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'migrate',
                        'options' => []
                    ],
                    [
                        'task' => 'typo3.surf:test:doctrine:migrate',
                        'node' => 'flow-2.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'migrate',
                        'options' => []
                    ],
                    [
                        'task' => 'typo3.surf:test:em:updatedatabase',
                        'node' => 'neos.example.com',
                        'application' => 'TYPO3 Neos Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'migrate',
                        'options' => []
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider applicationTaskDefinitions
     *
     * @param string $message
     * @param \Closure $initializeCallback
     * @param array $expectedExecutions
     */
    public function applicationTaskDefinitionsAreExecutedCorrectly(
        $message,
        $initializeCallback,
        array $expectedExecutions
    ): void {
        $executedTasks = [];
        $deployment = $this->buildDeployment($executedTasks);
        $workflow = $deployment->getWorkflow();

        $flowApplication = new Application('Neos Flow Application');
        $flowApplication
            ->addNode(new Node('flow-1.example.com'))
            ->addNode(new Node('flow-2.example.com'));

        $neosApplication = new Application('TYPO3 Neos Application');
        $neosApplication
            ->addNode(new Node('neos.example.com'));

        $deployment
            ->addApplication($flowApplication)
            ->addApplication($neosApplication)
            ->onInitialize($initializeCallback($workflow, [$flowApplication, $neosApplication]));

        $deployment->initialize();

        $workflow->run($deployment);

        self::assertEquals($expectedExecutions, $executedTasks, $message);
    }

    /**
     * Build a Deployment object with Workflow for testing
     *
     * @param array $executedTasks Register for executed tasks
     *
     * @return \TYPO3\Surf\Domain\Model\Deployment A configured Deployment for testing
     */
    protected function buildDeployment(array &$executedTasks = [])
    {
        $deployment = new Deployment('Test deployment');
        $mockLogger = $this->createMock(LoggerInterface::class);
        // Enable log to console to debug tests
        // $mockLogger->expects(self::any())->method('log')->will($this->returnCallback(function($message) {
        // 	echo $message . chr(10);
        // }));
        $deployment->setLogger($mockLogger);

        $mockTaskManager = $this->createMock(TaskManager::class);
        $mockTaskManager
            ->expects(self::any())
            ->method('execute')
            ->will(self::returnCallback(function ($task, Node $node, Application $application, Deployment $deployment, $stage, array $options = []) use (&$executedTasks): void {
                $executedTasks[] = [
                    'task' => $task,
                    'node' => $node->getName(),
                    'application' => $application->getName(),
                    'deployment' => $deployment->getName(),
                    'stage' => $stage,
                    'options' => $options
                ];
            }));

        $workflow = new SimpleWorkflow($mockTaskManager);
        $deployment->setWorkflow($workflow);

        return $deployment;
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

        $workflow->addTask('task1:package', 'package');

        $workflow->addTask('task1:initialize', 'initialize');
        $workflow->addTask('task3:initialize', 'initialize');
        $workflow->afterTask('task1:initialize', 'task2:initialize');

        $workflow->beforeStage('initialize', 'before1:initialize');
        $workflow->beforeStage('initialize', 'before3:initialize');
        $workflow->afterTask('before1:initialize', 'before2:initialize');

        $workflow->afterStage('initialize', 'after1:initialize');
        $workflow->afterStage('initialize', 'after3:initialize');
        $workflow->afterTask('after1:initialize', 'after2:initialize');

        $workflow->run($deployment);

        $expected = [
            [
                'task' => 'before1:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test deployment',
                'stage' => 'initialize',
                'options' => []
            ],
            [
                'task' => 'before2:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test deployment',
                'stage' => 'initialize',
                'options' => []
            ],
            [
                'task' => 'before3:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test deployment',
                'stage' => 'initialize',
                'options' => []
            ],
            [
                'task' => 'task1:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test deployment',
                'stage' => 'initialize',
                'options' => []
            ],
            [
                'task' => 'task2:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test deployment',
                'stage' => 'initialize',
                'options' => []
            ],
            [
                'task' => 'task3:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test deployment',
                'stage' => 'initialize',
                'options' => []
            ],
            [
                'task' => 'after1:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test deployment',
                'stage' => 'initialize',
                'options' => []
            ],
            [
                'task' => 'after2:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test deployment',
                'stage' => 'initialize',
                'options' => []
            ],
            [
                'task' => 'after3:initialize',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test deployment',
                'stage' => 'initialize',
                'options' => []
            ],
            [
                'task' => 'task1:package',
                'node' => 'flow-1.example.com',
                'application' => 'Neos Flow Application',
                'deployment' => 'Test deployment',
                'stage' => 'package',
                'options' => []
            ]
        ];

        self::assertEquals($expected, $executedTasks);
    }

    /**
     * @return array
     */
    public function taskRegistrationExamples(): array
    {
        return [
            'remove task in stage' => [
                function ($workflow, $application): void {
                    $workflow->addTask('task1:initialize', 'initialize');
                    $workflow->addTask('task2:package', 'package');

                    $workflow->removeTask('task1:initialize');
                },
                [
                    [
                        'task' => 'task2:package',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'package',
                        'options' => []
                    ]
                ]
            ],
            'remove task in before hook' => [
                function ($workflow, $application): void {
                    $workflow->addTask('task1:initialize', 'initialize');
                    $workflow->beforeTask('task1:initialize', 'task2:before');
                    $workflow->beforeTask('task1:initialize', 'task3:before');

                    $workflow->removeTask('task2:before');
                },
                [
                    [
                        'task' => 'task3:before',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ],
                    [
                        'task' => 'task1:initialize',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ]
                ]
            ],
            'remove task in after hook' => [
                function ($workflow, $application): void {
                    $workflow->addTask('task1:initialize', 'initialize');
                    $workflow->afterTask('task1:initialize', 'task2:after');
                    $workflow->afterTask('task1:initialize', 'task3:after');

                    $workflow->removeTask('task2:after');
                },
                [
                    [
                        'task' => 'task1:initialize',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ],
                    [
                        'task' => 'task3:after',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => []
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function taskRegistrationExamplesForDifferentApplications(): array
    {
        return [
            'remove task in stage for specific application' => [
                [
                    [
                        'application' => 'Neos Flow Application',
                        'node' => 'flow-1.example.com',
                        'callable' => function (Workflow $workflow, Application $application): void {
                            $workflow->addTask('task1:initialize', 'initialize', $application);
                            $workflow->addTask('task2:package', 'package', $application);
                            $workflow->afterTask('task2:package', 'task2:whatever', $application);
                            $workflow->removeTask('task2:whatever', $application);
                        },
                    ],
                    [
                        'application' => 'TYPO3 Application',
                        'node' => 'typo3.example.com',
                        'callable' => function (Workflow $workflow, Application $application): void {
                            $workflow->addTask('task1:initialize', 'initialize', $application);
                            $workflow->addTask('task2:package', 'package', $application);
                            $workflow->afterTask('task2:package', 'task2:whatever', $application);
                            $workflow->removeTask('task1:initialize', $application);
                        },
                    ],
                ],
                [
                    [
                        'task' => 'task1:initialize',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'initialize',
                        'options' => [],
                    ],
                    [
                        'task' => 'task2:package',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'package',
                        'options' => [],
                    ],
                    [
                        'task' => 'task2:package',
                        'node' => 'typo3.example.com',
                        'application' => 'TYPO3 Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'package',
                        'options' => [],
                    ],
                    [
                        'task' => 'task2:whatever',
                        'node' => 'typo3.example.com',
                        'application' => 'TYPO3 Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'package',
                        'options' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider taskRegistrationExamplesForDifferentApplications
     *
     * @param array $applications
     * @param array $expectedTasks
     *
     * @throws SurfException
     * @throws SurfException\InvalidConfigurationException
     */
    public function removeTaskRemovesTaskFromStagesForSpecificApplication(
        array $applications,
        array $expectedTasks
    ): void {
        $executedTasks = [];
        $deployment = $this->buildDeployment($executedTasks);
        $workflow = $deployment->getWorkflow();

        foreach ($applications as $applicationConfiguration) {
            $application = new Application($applicationConfiguration['application']);
            $application->addNode(new Node($applicationConfiguration['node']));
            $deployment->addApplication($application);
            $applicationConfiguration['callable']($workflow, $application);
        }

        $deployment->initialize();

        $workflow->run($deployment);

        self::assertEquals($expectedTasks, $executedTasks);
    }

    /**
     * @test
     * @dataProvider taskRegistrationExamples
     *
     * @param callable $callback
     * @param array $expectedTasks
     *
     * @throws SurfException
     * @throws SurfException\InvalidConfigurationException
     */
    public function removeTaskRemovesTaskFromStages($callback, $expectedTasks): void
    {
        $executedTasks = [];
        $deployment = $this->buildDeployment($executedTasks);
        $workflow = $deployment->getWorkflow();

        $flowApplication = new Application('Neos Flow Application');
        $flowApplication->addNode(new Node('flow-1.example.com'));

        $deployment->addApplication($flowApplication);
        $deployment->initialize();

        $callback($workflow, $flowApplication);

        $workflow->run($deployment);

        self::assertEquals($expectedTasks, $executedTasks);
    }

    /**
     * @return array
     */
    public function stageStepExamples(): array
    {
        return [
            'task in stage for specific application, task after stage for any application' => [
                function (Workflow $workflow, Application $application): void {
                    $workflow->addTask('task1:switch', 'switch', $application);
                    $workflow->afterStage('switch', 'task2:afterSwitch');
                },
                [
                    [
                        'task' => 'task1:switch',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'switch',
                        'options' => []
                    ],
                    [
                        'task' => 'task2:afterSwitch',
                        'node' => 'flow-1.example.com',
                        'application' => 'Neos Flow Application',
                        'deployment' => 'Test deployment',
                        'stage' => 'switch',
                        'options' => []
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider stageStepExamples
     */
    public function beforeAndAfterStageStepsAreIndependentOfApplications(callable $callback, array $expectedTasks): void
    {
        $executedTasks = [];
        $deployment = $this->buildDeployment($executedTasks);
        $workflow = $deployment->getWorkflow();

        $flowApplication = new Application('Neos Flow Application');
        $flowApplication->addNode(new Node('flow-1.example.com'));

        $deployment->addApplication($flowApplication);
        $deployment->initialize();

        $callback($workflow, $flowApplication);

        $workflow->run($deployment);

        self::assertEquals($expectedTasks, $executedTasks);
    }
}
