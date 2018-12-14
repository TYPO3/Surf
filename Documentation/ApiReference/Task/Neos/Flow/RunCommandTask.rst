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

    Example:
     $workflow
         ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\RunCommandTask::class, [
                 'command' => 'flow:help',
                 'arguments => [],
                 'ignoreErrors' => false,
                 'logOutput' => true,
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

    .. php:method:: rollback(Node $node, Application $application, Deployment $deployment, $options = [])

        Rollback the task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: buildCommandArguments($options)

        :type $options: array
        :param $options: The command options
        :returns: string The escaped arguments string

    .. php:method:: setShellCommandService(ShellCommandService $shellCommandService)

        :type $shellCommandService: ShellCommandService
        :param $shellCommandService:

    .. php:method:: configureOptions($options = [])

        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:
