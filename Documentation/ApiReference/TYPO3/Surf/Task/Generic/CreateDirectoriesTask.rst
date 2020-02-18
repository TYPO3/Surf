-------------------------------------------------
TYPO3\\Surf\\Task\\Generic\\CreateDirectoriesTask
-------------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Generic

.. php:class:: CreateDirectoriesTask

    Creates directories for a release.

    It takes the following options:

    * baseDirectory (optional) - Can be set as base path.
    * directories - An array of directories to create. The paths can be relative to the baseDirectory, if set.

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\Generic\CreateDirectoriesTask', [
                 'baseDirectory' => '/var/www/outerspace',
                 'directories' => [
                     'uploads/spaceship',
                     'uploads/freighter',
                     '/tmp/outerspace/lonely_planet'
                 ]
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
