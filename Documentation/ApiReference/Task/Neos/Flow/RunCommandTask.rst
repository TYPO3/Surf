---------------------------------------------
TYPO3\\Surf\\Task\\Neos\\Flow\\RunCommandTask
---------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Neos\\Flow

.. php:class:: RunCommandTask

    This task runs Neos Flow commands

    It takes the following options:

    * command (required)
    * arguments
    * ignoreErrors (optional)
    * logOutput (optional)
    * phpBinaryPathAndFilename (optional) - path to the php binary default php

    Example:
     $workflow
         ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\RunCommandTask::class, [
                 'command' => 'flow:help',
                 'arguments => [],
                 'ignoreErrors' => false,
                 'logOutput' => true,
                 'phpBinaryPathAndFilename', '/path/to/php',
             ]
         );

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
