----------------------------------------
TYPO3\\Surf\\Task\\SourceforgeUploadTask
----------------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: SourceforgeUploadTask

    A task for uploading to sourceforge.

    It takes the following options:

    * sourceforgeProjectName - The project name at SourceForge.
    * sourceforgeUserName - The user name to log in at SourceForge.
    * sourceforgePackageName - The package name of the package that shouldd be uploaded.
    * version - The version of the project.
    * files - An array with files to upload to SourceForge.

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\SourceforgeUploadTask', [
                 'sourceforgeProjectName' => 'enterprise',
                 'sourceforgeUserName' => 'picard',
                 'sourceforgePackageName' => 'nextGeneration',
                 'version' => '1.0.0',
                 'files' => [
                     '/var/borg',
                     '/var/q',
                     '/var/data'
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

    .. php:method:: checkOptionsForValidity($options)

        Check if all required options are given

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
