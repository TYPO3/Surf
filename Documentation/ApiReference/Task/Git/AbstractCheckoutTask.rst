--------------------------------------------
TYPO3\\Surf\\Task\\Git\\AbstractCheckoutTask
--------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Git

.. php:class:: AbstractCheckoutTask

    An abstract git checkout task

    .. php:attr:: shell

        protected ShellCommandService

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

    .. php:method:: resolveSha1(Node $node, Deployment $deployment, $options)

        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: executeOrSimulateGitCloneOrUpdate($checkoutPath, Node $node, Deployment $deployment, $options)

        :type $checkoutPath: string
        :param $checkoutPath:
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: executeOrSimulatePostGitCheckoutCommands($gitPath, $sha1, Node $node, Deployment $deployment, $options)

        :param $gitPath:
        :param $sha1:
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: setShellCommandService(ShellCommandService $shellCommandService)

        :type $shellCommandService: ShellCommandService
        :param $shellCommandService:

    .. php:method:: execute(Node $node, Application $application, Deployment $deployment, $options = [])

        Executes this action

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
