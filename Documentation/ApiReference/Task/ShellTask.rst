----------------------------
TYPO3\\Surf\\Task\\ShellTask
----------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: ShellTask

    A task to execute shell commands on the remote host.

    It takes the following options:

    * command - The command that should be executed on the remote host.
    * rollbackCommand (optional) - The command that reverses the changes.
    * ignoreErrors (optional) - If true, ignore errors during execution. Default is true.
    * logOutput (optional) - If true, output the log. Default is false.

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\ShellTask', [
                 'command' => 'mkdir -p /var/www/outerspace',
                 'rollbackCommand' => 'rm -rf /var/www/outerspace'
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

        Rollback this task

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

    .. php:method:: replacePaths(Application $application, Deployment $deployment, $command)

        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $command: string
        :param $command:
        :returns: mixed

    .. php:method:: setShellCommandService(ShellCommandService $shellCommandService)

        :type $shellCommandService: ShellCommandService
        :param $shellCommandService:

    .. php:method:: configureOptions($options = [])

        :type $options: array
        :param $options:
        :returns: array
