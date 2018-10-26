-----------------------------------------
TYPO3\\Surf\\Domain\\Service\\TaskManager
-----------------------------------------

.. php:namespace: TYPO3\\Surf\\Domain\\Service

.. php:class:: TaskManager

    A task manager

    .. php:attr:: taskHistory

        protected array

        Task history for rollback

    .. php:method:: execute($taskName, Node $node, Application $application, Deployment $deployment, $stage, $options = [], $definedTaskName = '')

        Execute a task

        :type $taskName: string
        :param $taskName:
        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $stage: string
        :param $stage:
        :type $options: array
        :param $options: Local task options
        :type $definedTaskName: string
        :param $definedTaskName:

    .. php:method:: rollback()

        Rollback all tasks stored in the task history in reverse order

    .. php:method:: reset()

        Reset the task history

    .. php:method:: overrideOptions($taskName, Deployment $deployment, Node $node, Application $application, $taskOptions)

        Override options for a task

        The order of the options is:

        Deployment, Node, Application, Task

        A task option will always override more global options from the
        Deployment, Node or Application.

        Global options for a task should be prefixed with the task name to prevent
        naming issues between different tasks. For example passing a special
        option to the GitCheckoutTask could be expressed like
        GitCheckoutTask::class . '[sha1]' => '1234...'.

        :type $taskName: string
        :param $taskName:
        :type $deployment: Deployment
        :param $deployment:
        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $taskOptions: array
        :param $taskOptions:
        :returns: array

    .. php:method:: createTaskInstance($taskName)

        Create a task instance from the given task name

        :type $taskName: string
        :param $taskName:
        :returns: \TYPO3\Surf\Domain\Model\Task

    .. php:method:: mapTaskNameToTaskClass($taskName)

        Map the task name to the proper task class

        :type $taskName: string
        :param $taskName:
        :returns: string
