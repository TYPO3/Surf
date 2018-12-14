-----------------------------------
TYPO3\\Surf\\Task\\DumpDatabaseTask
-----------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: DumpDatabaseTask

    This task dumps a complete database from a source system to a target system.

    It takes the following options:

    * sourceHost - The host on which the source database is located.
    * sourceUser - The database user of the source database.
    * sourcePassword - The password of the source user.
    * sourceDatabase - The source database.
    * targetHost - The host on which the target database is located.
    * targetUser - The database user og the target database.
    * targetPassword - The password of the target user.
    * targetDatabase - The target database.

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\DumpDatabaseTask', [
                 sourceHost => 'from.outerspace.all',
                 sourceUser => 'e_t',
                 sourcePassword => 'phoneHome',
                 sourceDatabase => 'spaceship',
                 targetHost => 'localhost',
                 targetUser => 'elliot',
                 targetPassword => 'human',
                 targetDatabase => 'house'
             ]
         );

    .. php:attr:: requiredOptions

        protected array

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

    .. php:method:: assertRequiredOptionsExist($options)

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

    .. php:method:: configureOptions($options = [])

        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:
