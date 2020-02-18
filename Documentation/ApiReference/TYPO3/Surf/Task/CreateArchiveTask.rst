------------------------------------
TYPO3\\Surf\\Task\\CreateArchiveTask
------------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: CreateArchiveTask

    A task for creating an zip / tar.gz / tar.bz2 archive.

    Needs the following options:

    * sourceDirectory - The directory which should be compressed.
    * targetFile - The target file. The file ending defines the format. Supported are .zip, .tar.gz, .tar.bz2.
    * baseDirectory - The base directory in the compressed archive in which all files should reside in.
    * exclude - An array of exclude patterns, as being understood by tar (optional)

    This task needs the following unix command line tools:

    * tar / gnutar
    * zip

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\CreateArchiveTask', [
                 'sourceDirectory' => '/var/www/outerspace',
                 'targetFile' => '/var/www/outerspace.zip',
                 'baseDirectory' => 'compressedSpace',
                 'exclude' => [
                     '*.bak'
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

    .. php:method:: checkOptionsForValidity($options)

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

    .. php:method:: simulate(Node $node, Application $application, Deployment $deployment, $options = [])

        Simulate this task (e.g. by logging commands it would execute)

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

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:
