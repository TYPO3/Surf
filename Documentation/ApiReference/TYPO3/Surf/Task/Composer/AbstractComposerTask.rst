-------------------------------------------------
TYPO3\\Surf\\Task\\Composer\\AbstractComposerTask
-------------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Composer

.. php:class:: AbstractComposerTask

    Installs the composer packages based on a composer.json file in the projects root folder

    .. php:attr:: command

        protected string

        Command to run

    .. php:attr:: arguments

        protected array

        Arguments for the command

    .. php:attr:: suffix

        protected array

        Suffix for the command

    .. php:attr:: shell

        protected ShellCommandService

    .. php:method:: execute(Node $node, Application $application, Deployment $deployment, $options = [])

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

    .. php:method:: buildComposerCommands($manifestPath, $options)

        Build the composer command in the given $path.

        :type $manifestPath: string
        :param $manifestPath:
        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: composerManifestExists($path, Node $node, Deployment $deployment)

        Checks if a composer manifest exists in the directory at the given path.

        If no manifest exists, a log message is recorded.

        :type $path: string
        :param $path:
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :returns: bool

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:

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
