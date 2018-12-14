-------------------------------------------------------
TYPO3\\Surf\\Task\\Neos\\Flow\\SymlinkConfigurationTask
-------------------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Neos\\Flow

.. php:class:: SymlinkConfigurationTask

    This task takes care of symlinking the shared Production Configuration

    Note: this might cause problems with concurrent access due to the cached configuration inside this directory.

    It takes no options

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

    .. php:method:: configureOptions($options = [])

        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:
