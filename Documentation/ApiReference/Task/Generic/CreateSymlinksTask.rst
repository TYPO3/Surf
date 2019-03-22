----------------------------------------------
TYPO3\\Surf\\Task\\Generic\\CreateSymlinksTask
----------------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Generic

.. php:class:: CreateSymlinksTask

    Creates symlinks on target node.

    It takes the following options:

    * symlinks - An array of symlinks to create. The array index is the link to be created (relative to the current application release path). The value is the path to the existing file/directory (absolute or relative to the link).

    Example:
     $options['symlinks'] = array(
         'Web/foobar' => '/tmp/foobar', # An absolute link
         'Web/foobaz' => '../../../shared/Data/foobaz', # A relative link into the shared folder
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
