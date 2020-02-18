--------------------------------------------
TYPO3\\Surf\\Domain\\Model\\RollbackWorkflow
--------------------------------------------

.. php:namespace: TYPO3\\Surf\\Domain\\Model

.. php:class:: RollbackWorkflow

    .. php:attr:: taskManager

        protected \TYPO3\Surf\Domain\Service\TaskManager

    .. php:attr:: tasks

        protected array

    .. php:method:: run(Deployment $deployment)

        :type $deployment: Deployment
        :param $deployment:

    .. php:method:: configureRollbackTasks(Deployment $deployment)

        :type $deployment: Deployment
        :param $deployment:

    .. php:method:: getName()

        :returns: string

    .. php:method:: __construct(TaskManager $taskManager = null)

        :type $taskManager: TaskManager
        :param $taskManager:

    .. php:method:: removeTask($removeTask, Application $application = null)

        Remove the given task from all stages and applications

        :type $removeTask: string
        :param $removeTask:
        :type $application: Application
        :param $application: if given, task is only removed from application
        :returns: \TYPO3\Surf\Domain\Model\Workflow

    .. php:method:: forStage($stage, $tasks)

        :type $stage: string
        :param $stage:
        :type $tasks: array|string
        :param $tasks:
        :returns: \TYPO3\Surf\Domain\Model\Workflow

    .. php:method:: addTaskToStage($tasks, $stage, Application $application = null, $step = 'tasks')

        Add the given tasks to a step in a stage and optionally a specific
        application

        The tasks will be executed for the given stage. If an application is
        given,
        the tasks will be executed only for the stage and application.

        :type $tasks: array|string
        :param $tasks:
        :type $stage: string
        :param $stage: The name of the stage when this task shall be executed
        :type $application: Application
        :param $application: If given the task will be specific for this application
        :type $step: string
        :param $step: A stage has three steps "before", "tasks" and "after"

    .. php:method:: addTask($tasks, $stage, Application $application = null)

        Add the given tasks for a stage and optionally a specific application

        The tasks will be executed for the given stage. If an application is
        given,
        the tasks will be executed only for the stage and application.

        :type $tasks: array|string
        :param $tasks:
        :type $stage: string
        :param $stage: The name of the stage when this task shall be executed
        :type $application: Application
        :param $application: If given the task will be specific for this application
        :returns: \TYPO3\Surf\Domain\Model\Workflow

    .. php:method:: afterTask($task, $tasks, Application $application = null)

        Add tasks that shall be executed after the given task

        The execution will not depend on a stage but on an optional application.

        :type $task: string
        :param $task:
        :type $tasks: array|string
        :param $tasks:
        :type $application: Application
        :param $application:
        :returns: \TYPO3\Surf\Domain\Model\Workflow

    .. php:method:: beforeTask($task, $tasks, Application $application = null)

        Add tasks that shall be executed before the given task

        The execution will not depend on a stage but on an optional application.

        :type $task: string
        :param $task:
        :type $tasks: array|string
        :param $tasks:
        :type $application: Application
        :param $application:
        :returns: \TYPO3\Surf\Domain\Model\Workflow

    .. php:method:: defineTask($taskName, $baseTask, $options)

        Define a new task based on an existing task by setting options

        :type $taskName: string
        :param $taskName:
        :type $baseTask: string
        :param $baseTask:
        :type $options: array
        :param $options:
        :returns: \TYPO3\Surf\Domain\Model\Workflow

    .. php:method:: beforeStage($stage, $tasks, Application $application = null)

        Add tasks that shall be executed before the given stage

        :type $stage: string
        :param $stage:
        :type $tasks: array|string
        :param $tasks:
        :type $application: Application
        :param $application:
        :returns: \TYPO3\Surf\Domain\Model\Workflow

    .. php:method:: afterStage($stage, $tasks, Application $application = null)

        Add tasks that shall be executed after the given stage

        :type $stage: string
        :param $stage:
        :type $tasks: array|string
        :param $tasks:
        :type $application: Application
        :param $application:
        :returns: \TYPO3\Surf\Domain\Model\Workflow

    .. php:method:: setTaskOptions($taskName, $options)

        Override options for given task

        :type $taskName: string
        :param $taskName:
        :type $options: array
        :param $options:
        :returns: \TYPO3\Surf\Domain\Model\Workflow

    .. php:method:: getTasks()

        Returns list of all registered tasks

        :returns: array

    .. php:method:: executeStage($stage, Node $node, Application $application, Deployment $deployment)

        Execute a stage for a node and application

        :type $stage: string
        :param $stage:
        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:

    .. php:method:: executeTask($task, Node $node, Application $application, Deployment $deployment, $stage, $callstack = [])

        Execute a task and consider configured before / after "hooks"

        Will also execute tasks that are registered to run before or after this
        task.

        :type $task: string
        :param $task:
        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $stage: string
        :param $stage:
        :type $callstack: array
        :param $callstack:
