--------------------------------------
TYPO3\\Surf\\Task\\Transfer\\RsyncTask
--------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Transfer

.. php:class:: RsyncTask

    A rsync transfer task

    Copies the application assets from the application workspace to the node using rsync.

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

    .. php:method:: getExcludeFlags($rsyncExcludes)

        Generates the --exclude flags for a given array of exclude patterns

        Example: ['foo', '/bar'] => --exclude 'foo' --exclude '/bar'

        :type $rsyncExcludes: array
        :param $rsyncExcludes: An array of patterns to be excluded
        :returns: string

    .. php:method:: setShellCommandService(ShellCommandService $shellCommandService)

        :type $shellCommandService: ShellCommandService
        :param $shellCommandService:
