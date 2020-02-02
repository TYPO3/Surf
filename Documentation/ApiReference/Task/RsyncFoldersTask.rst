-----------------------------------
TYPO3\\Surf\\Task\\RsyncFoldersTask
-----------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: RsyncFoldersTask

    A task to synchronize folders from the machine that runs Surf to a remote host by using Rsync.

    It takes the following options:

    * folders - An array with folders to synchronize. The key holds the source folder, the value holds the target folder.
      The target folder must have an absolute path.
    * username (optional) - The username to log in on the remote host.
    * ignoreErrors (optional) - If true, ignore errors during execution. Default is true.
    * logOutput (optional) - If true, output the log. Default is false.

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\RsyncFoldersTask', [
                 'folders' => [
                     ['uploads/spaceship', '/var/www/outerspace/uploads/spaceship'],
                     ['uploads/freighter', '/var/www/outerspace/uploads/freighter'],
                     ['/tmp/outerspace/lonely_planet', '/var/www/outerspace/uploads/lonely_planet']
                     '/tmp/outerspace/lonely_planet' => '/var/www/outerspace/uploads/lonely_planet'
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
