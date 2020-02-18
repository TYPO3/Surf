--------------------------------------
TYPO3\\Surf\\Task\\CleanupReleasesTask
--------------------------------------

.. php:namespace: TYPO3\\Surf\\Task

.. php:class:: CleanupReleasesTask

    A cleanup task to delete old (unused) releases.

    Cleanup old releases by listing all releases and keeping a configurable number of old releases (application option "keepReleases"). The current and previous release (if one exists) are protected from removal.

    Note: There is no rollback for this cleanup, so we have to be sure not to delete any live or referenced releases.

    It takes the following options:

    * keepReleases - The number of releases to keep.
    * onlyRemoveReleasesOlderThanXSeconds - Remove only those releases older than the defined seconds

    Example configuration:
        $application->setOption('keepReleases', 2);
        $application->setOption('onlyRemoveReleasesOlderThan', '121 seconds ago')
    Note: There is no rollback for this cleanup, so we have to be sure not to delete any live or referenced releases.

    .. php:attr:: shell

        protected ShellCommandService

    .. php:method:: __construct(ClockInterface $clock = null)

        CleanupReleasesTask constructor.

        :type $clock: ClockInterface
        :param $clock:

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
        :returns: void|null

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

    .. php:method:: removeReleasesByAge($options, $removableReleases)

        :type $options: array
        :param $options:
        :type $removableReleases: array
        :param $removableReleases:
        :returns: array

    .. php:method:: removeReleasesByNumber($options, $removableReleases)

        :type $options: array
        :param $options:
        :type $removableReleases: array
        :param $removableReleases:
        :returns: array

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
