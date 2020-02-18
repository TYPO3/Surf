---------------------------------------------
TYPO3\\Surf\\Task\\TYPO3\\CMS\\RunCommandTask
---------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\TYPO3\\CMS

.. php:class:: RunCommandTask

    Task for running arbitrary TYPO3 commands

    .. php:attr:: workingDirectory

        protected string

        The working directory. Either local or remote, and probably in a special
        application root directory

    .. php:attr:: targetNode

        protected Node

        Localhost or deployment target node

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

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:

    .. php:method:: executeCliCommand($cliArguments, Node $node, CMS $application, Deployment $deployment, $options = [])

        Execute this task

        :type $cliArguments: array
        :param $cliArguments:
        :type $node: Node
        :param $node:
        :type $application: CMS
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: bool|mixed

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

    .. php:method:: determineWorkingDirectoryAndTargetNode(Node $node, Application $application, Deployment $deployment, $options = [])

        Determines the path to the working directory and the target node by given
        options

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: getAvailableCliPackage(Node $node, CMS $application, Deployment $deployment, $options = [])

        :type $node: Node
        :param $node:
        :type $application: CMS
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: string

    .. php:method:: getConsoleScriptFileName(Node $node, CMS $application, Deployment $deployment, $options = [])

        :type $node: Node
        :param $node:
        :type $application: CMS
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: string

    .. php:method:: packageExists($packageKey, Node $node, CMS $application, Deployment $deployment, $options = [])

        Checks if a package exists in the packages directory

        :type $packageKey: string
        :param $packageKey:
        :type $node: Node
        :param $node:
        :type $application: CMS
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: bool

    .. php:method:: directoryExists($directory, Node $node, CMS $application, Deployment $deployment, $options = [])

        Checks if a given directory exists.

        :type $directory: string
        :param $directory:
        :type $node: Node
        :param $node:
        :type $application: CMS
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: bool

    .. php:method:: fileExists($pathAndFileName, Node $node, CMS $application, Deployment $deployment, $options = [])

        Checks if a given file exists.

        :type $pathAndFileName: string
        :param $pathAndFileName:
        :type $node: Node
        :param $node:
        :type $application: CMS
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: bool

    .. php:method:: ensureApplicationIsTypo3Cms(Application $application)

        :type $application: Application
        :param $application:

    .. php:method:: getCliDispatchScriptFileName($options = [])

        :type $options: array
        :param $options:
        :returns: string

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
