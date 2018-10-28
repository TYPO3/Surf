----------------------------------------
TYPO3\\Surf\\Task\\CreateDirectoriesTask
----------------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: CreateDirectoriesTask

    A task to create initial directories and the release directory for the current release.

    This task will automatically create needed directories and create a symlink to the upcoming release, called "next".

    It doesn't take any options, you have to configure the application.

    Example:
     $application
         ->setOption('deploymentPath', '/var/www/outerspace');

    .. php:attr:: shell

        protected ShellCommandService

    .. php:method:: execute(Node $node, Application $application, Deployment $deployment, $options = [])

        Executes this task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: simulate(Node $node, Application $application, Deployment $deployment, $options = [])

        Simulate this task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: rollback(Node $node, Application $application, Deployment $deployment, $options = [])

        Rollback this task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: setShellCommandService(ShellCommandService $shellCommandService)

        :type $shellCommandService: ShellCommandService
        :param $shellCommandService:
