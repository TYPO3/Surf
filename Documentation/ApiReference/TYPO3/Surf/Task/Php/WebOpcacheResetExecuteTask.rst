--------------------------------------------------
TYPO3\\Surf\\Task\\Php\\WebOpcacheResetExecuteTask
--------------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Php

.. php:class:: WebOpcacheResetExecuteTask

    A task to reset the PHP opcache by executing a prepared script with an HTTP request.

    It takes the following options:

    * baseUrl - The path where the script is located.
    * scriptIdentifier - The name of the script. Default is a random string. See `WebOpcacheResetCreateScriptTask`
      for more information.

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask', [
                 'baseUrl' => '/var/www/outerspace',
                 'scriptIdentifier' => 'eraseAllHumans',
                 'stream_context' => [
                        'http' => [
                             'header' => 'Authorization: Basic '.base64_encode("username:password"),
                        ],
                 ],
             ]
         );

    .. php:method:: __construct(FilesystemInterface $filesystem = null)

        WebOpcacheResetCreateScriptTask constructor.

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
        :param $options: Supported options: "baseUrl" (required) and "scriptIdentifier" (is passed by the create script task)

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:

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
