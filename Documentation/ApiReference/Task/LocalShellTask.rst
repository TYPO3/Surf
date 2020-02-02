---------------------------------
TYPO3\\Surf\\Task\\LocalShellTask
---------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: LocalShellTask

    A shell task for local packaging.

    It takes the following options:

    * command - The command to execute.
    * rollbackCommand (optional) - The command to execute as a rollback.
    * ignoreErrors (optional) - If true, ignore errors during execution. Default is true.
    * logOutput (optional) - If true, output the log. Default is false.

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\LocalShellTask', [
                 'command' => mkdir -p /var/wwww/outerspace',
                 'rollbackCommand' => 'rm -rf /Var/www/outerspace'
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
