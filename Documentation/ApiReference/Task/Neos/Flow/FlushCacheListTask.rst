-------------------------------------------------
TYPO3\\Surf\\Task\\Neos\\Flow\\FlushCacheListTask
-------------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Neos\\Flow

.. php:class:: FlushCacheListTask

    This tasks clears the list of Flow Framework cache

    It takes the following options:

    * flushCacheList (required) - An array with extension keys to install.

    Example:
     $workflow
         ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\FlushCacheListTask::class, [
                 'flushCacheList' => [
                     'Neos_Fusion_Content',
                     'Flow_Session_MetaData',
                     'Flow_Session_Storage'
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
