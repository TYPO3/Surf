---------------------------------------------
TYPO3\\Surf\\Task\\Neos\\Neos\\ImportSiteTask
---------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Neos\\Neos

.. php:class:: ImportSiteTask

    This task imports a given site into a neos project by running site:import

    This command expects a Sites.xml file to be located in the private resources directory of the given package
    (Resources/Private/Content/Sites.xml)

    It takes the following arguments:

    * sitePackageKey (required)

    Example:
     $workflow
         ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\ImportSiteTask::class, [
                 'sitePackageKey' => 'Neos.ExampleSite'
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
