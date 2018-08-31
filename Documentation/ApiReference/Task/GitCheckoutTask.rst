----------------------------------
TYPO3\\Surf\\Task\\GitCheckoutTask
----------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: GitCheckoutTask

    A Git checkout task.

    It takes the following options:

    * repositoryUrl - The repository to check out.
    * hardClean (optional) - If true, the task performs a hard clean. Default is true.

    Example:
     $application->setOption('repositoryUrl', 'git@github.com:TYPO3/Surf.git');

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

    .. php:method:: rollback(Node $node, Application $application, Deployment $deployment, $options = [])

        Rollback this task by removing the revision file

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

    .. php:method:: resolveSha1(Node $node, Deployment $deployment, $options)

        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: executeOrSimulateGitCloneOrUpdate($checkoutPath, Node $node, Deployment $deployment, $options)

        :type $checkoutPath: string
        :param $checkoutPath:
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: executeOrSimulatePostGitCheckoutCommands($gitPath, $sha1, Node $node, Deployment $deployment, $options)

        :param $gitPath:
        :param $sha1:
        :type $node: Node
        :param $node:
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
