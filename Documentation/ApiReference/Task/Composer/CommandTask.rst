----------------------------------------
TYPO3\\Surf\\Task\\Composer\\CommandTask
----------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Composer

.. php:class:: CommandTask

    Runs a custom composer command

    It takes the following options:

    * composerCommandPath - Path to the composer binary
    * command - The composer command to run
    * nodeName - The name of the node where the composer command should run.
    * arguments (optional) - Array of arguments to pass to the composer command, default `--no-ansi --no-interaction`
    * additionalArguments (optional) - Array of additional arguments to pass to composer and keep default arguments
    * suffix (optional) - Array, string or null with the suffix command, either `['2>&1']`, `[]`, `'2>&1'`, `''` or `null`
    * useApplicationWorkspace (optional) - If true Surf uses the workspace path, else it uses the release path of the application.

    Example:
     $workflow->defineTask('My\\Distribution\\DefinedTask\\RunBuildScript', \TYPO3\Surf\Task\Composer\CommandTask::class, [
         'composerCommandPath' => '/usr/local/bin/composer',
         'nodeName' => 'localhost',
         'command' => 'run-script',
         'additionalArguments' => ['build'],
         'useApplicationWorkspace' => true
     ]);
     $workflow->afterTask('TYPO3\\Surf\\DefinedTask\\Composer\\LocalInstallTask', 'My\\Distribution\\DefinedTask\\RunBuildScript', $application);

     `composer 'run-script' '--no-ansi' '--no-interaction' 'build' 2>&1$`

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

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:

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
