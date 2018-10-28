--------------------------------------
TYPO3\\Surf\\Task\\CleanupReleasesTask
--------------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: CleanupReleasesTask

    A cleanup task to delete old (unused) releases.

    Cleanup old releases by listing all releases and keeping a configurable number of old releases (application option "keepReleases"). The current and previous release (if one exists) are protected from removal.

    Note: There is no rollback for this cleanup, so we have to be sure not to delete any live or referenced releases.

    It takes the following options:

    * keepReleases - The number of releases to keep.

    Example configuration:
        $application->setOption('keepReleases', 2);
    Note: There is no rollback for this cleanup, so we have to be sure not to delete any live or referenced releases.

    .. php:attr:: shell

        protected ShellCommandService

    .. php:method:: execute(Node $node, Application $application, Deployment $deployment, $options = [])

        Execute this task

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

    .. php:method:: setShellCommandService(ShellCommandService $shellCommandService)

        :type $shellCommandService: ShellCommandService
        :param $shellCommandService:

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
