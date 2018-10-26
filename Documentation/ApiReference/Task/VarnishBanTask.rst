---------------------------------
TYPO3\\Surf\\Task\\VarnishBanTask
---------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: VarnishBanTask

    Task for banning in Varnish, should be used for Varnish 3.x.

    It takes the following options:

    * secretFile (optional) - Path to the secret file, defaults to "/etc/varnish/secret".
    * banUrl (optional) - URL (pattern) to ban, defaults to ".*".
    * varnishadm (optional) - Path to the varnishadm utility, defaults to "/usr/bin/varnishadm".

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\VarnishBanTask', [
                 'secretFile' => '/etc/varnish/secret',
                 'banUrl' => '.*',
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
