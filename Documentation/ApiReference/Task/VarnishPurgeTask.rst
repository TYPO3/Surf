-----------------------------------
TYPO3\\Surf\\Task\\VarnishPurgeTask
-----------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: VarnishPurgeTask

    Task for purging in Varnish, should be used for Varnish 2.x

    It takes the following options:

    * secretFile (optional) - Path to the secret file, defaults to "/etc/varnish/secret".
    * purgeUrl (optional) - URL (pattern) to purge, defaults to ".".
    * varnishadm (optional) - Path to the varnishadm utility, defaults to "/usr/bin/varnishadm".

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\VarnishPurgeTask', [
                 'secretFile' => '/etc/varnish/secret',
                 'purgeUrl' => '.',
                 'varnishadm' => '/usr/bin/varnishadm'
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
