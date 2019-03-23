-------------------------------------------------------
TYPO3\\Surf\\Task\\Php\\WebOpcacheResetCreateScriptTask
-------------------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Php

.. php:class:: WebOpcacheResetCreateScriptTask

    Create a script to reset the PHP opcache.

    The task creates a temporary script (locally in the release workspace directory) for resetting the PHP opcache in a later web request. A secondary task will execute an HTTP request and thus execute the script.

    The opcache reset has to be done in the webserver process, so a simple CLI command would not help.

    It takes the following options:

    * scriptBasePath (optional) - The path where the script should be created. Default is `<Workspace Path>/Web`.
    * scriptIdentifier (optional) - The name of the script. Default is a random string.

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask', [
                 'scriptBasePath' => '/var/www/outerspace',
                 'scriptIdentifier' => 'eraseAllHumans'
             ]
         );

    .. php:attr:: shell

        protected ShellCommandService

    .. php:method:: __construct(RandomBytesGeneratorInterface $randomBytesGenerator = null, FilesystemInterface $filesystem = null)

        WebOpcacheResetCreateScriptTask constructor.

        :type $randomBytesGenerator: RandomBytesGeneratorInterface
        :param $randomBytesGenerator:
        :type $filesystem: FilesystemInterface
        :param $filesystem:

    .. php:method:: execute(Node $node, Application $application, Deployment $deployment, $options = [])

        Execute this task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options: Supported options: "scriptBasePath" and "scriptIdentifier"

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

    .. php:method:: simulate(Node $node, Application $application, Deployment $deployment, $options = [])

        Simulate this task (e.g. by logging commands it would execute)

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
