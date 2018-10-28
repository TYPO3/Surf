--------------------------------------------
TYPO3\\Surf\\Domain\\Model\\FailedDeployment
--------------------------------------------

.. php:namespace: TYPO3\\Surf\\Domain\\Model

.. php:class:: FailedDeployment

    Representing a failed deployment

    This class does nothing

    .. php:attr:: name

        protected string

        The name of this deployment

    .. php:attr:: workflow

        protected \TYPO3\Surf\Domain\Model\Workflow

        The workflow used for this deployment

    .. php:attr:: applications

        protected Application[]

        The applications deployed with this deployment

    .. php:attr:: logger

        protected LoggerInterface

        A logger instance used to log messages during deployment

    .. php:attr:: releaseIdentifier

        protected string

        The release identifier will be created on each deployment

    .. php:attr:: dryRun

        protected string

        TRUE if the deployment should be simulated

    .. php:attr:: initCallbacks

        protected array

        Callbacks that should be executed after initialization

    .. php:attr:: status

        protected int

        Tells if the deployment ran successfully or failed

    .. php:attr:: initialized

        protected bool

    .. php:attr:: options

        protected array

        The options

    .. php:attr:: deploymentBasePath

        protected string

        The deployment declaration base path for this deployment

    .. php:attr:: workspacesBasePath

        protected string

        The base path to the local workspaces when packaging for deployment
        (may be temporary directory)

    .. php:attr:: temporaryPath

        protected string

        The base path to a temporary directory

    .. php:method:: __construct($name = null)

        Constructor

        :type $name: string
        :param $name:

    .. php:method:: initialize()

        Initialize the deployment
        noop

    .. php:method:: deploy()

        Run this deployment
        noop

    .. php:method:: simulate()

        Simulate this deployment without executing tasks
        noop

    .. php:method:: getStatus()

        Get the current deployment status

        :returns: int One of the Deployment::STATUS_* constants

    .. php:method:: onInitialize($callback)

        Add a callback to the initialization

        :type $callback: callable
        :param $callback:
        :returns: \TYPO3\Surf\Domain\Model\Deployment

    .. php:method:: getApplicationReleasePath(Application $application)

        :type $application: Application
        :param $application:
        :returns: string

    .. php:method:: getName()

        Get the Deployment's name

        :returns: string The Deployment's name

    .. php:method:: setName($name)

        Sets the deployment name

        :type $name: string
        :param $name: The deployment name
        :returns: \TYPO3\Surf\Domain\Model\Deployment The current deployment instance for chaining

    .. php:method:: getNodes()

        Get all nodes of this deployment

        :returns: Node[] The deployment nodes with all application nodes

    .. php:method:: getNode($name)

        Get a node by name

        In the special case "localhost" an ad-hoc Node with hostname "localhost"
        is returned.

        :param $name:
        :returns: \TYPO3\Surf\Domain\Model\Node The Node or NULL if no Node with the given name was found

    .. php:method:: getApplications()

        Get all applications for this deployment

        :returns: Application[]

    .. php:method:: addApplication(Application $application)

        Add an application to this deployment

        :type $application: Application
        :param $application: The application to add
        :returns: \TYPO3\Surf\Domain\Model\Deployment The current deployment instance for chaining

    .. php:method:: getWorkflow()

        Get the deployment workflow

        :returns: \TYPO3\Surf\Domain\Model\Workflow The deployment workflow

    .. php:method:: setWorkflow($workflow)

        Sets the deployment workflow

        :type $workflow: \TYPO3\Surf\Domain\Model\Workflow
        :param $workflow: The workflow to set
        :returns: \TYPO3\Surf\Domain\Model\Deployment The current deployment instance for chaining

    .. php:method:: setLogger(LoggerInterface $logger)

        :type $logger: LoggerInterface
        :param $logger:
        :returns: \TYPO3\Surf\Domain\Model\Deployment

    .. php:method:: getLogger()

        :returns: LoggerInterface

    .. php:method:: getReleaseIdentifier()

        Get the deployment release identifier

        This gets the current release identifier when running a deployment.

        :returns: string The release identifier

    .. php:method:: isDryRun()

        :returns: bool TRUE If the deployment is run in "dry run" mode

    .. php:method:: setDryRun($dryRun)

        Set the dry run mode for this deployment

        :type $dryRun: bool
        :param $dryRun:
        :returns: \TYPO3\Surf\Domain\Model\Deployment The current deployment instance for chaining

    .. php:method:: setStatus($status)

        :type $status: int
        :param $status:
        :returns: \TYPO3\Surf\Domain\Model\Deployment

    .. php:method:: isInitialized()

        :returns: bool TRUE If the deployment is initialized

    .. php:method:: getOptions()

        Get all options defined on this application instance

        The options will include the deploymentPath and sharedPath for unified
        option handling.

        :returns: array An array of options indexed by option key

    .. php:method:: getOption($key)

        Get an option defined on the deployment

        :type $key: string
        :param $key:
        :returns: mixed

    .. php:method:: hasOption($key)

        Test if an option was set for this deployment

        :type $key: string
        :param $key: The option key
        :returns: bool TRUE If the option was set

    .. php:method:: setOptions($options)

        Sets all options for the deployment

        :type $options: array
        :param $options: The options to set indexed by option key
        :returns: \TYPO3\Surf\Domain\Model\Deployment The current instance for chaining

    .. php:method:: setOption($key, $value)

        Set an option for the deployment

        :type $key: string
        :param $key: The option key
        :type $value: mixed
        :param $value: The option value
        :returns: \TYPO3\Surf\Domain\Model\Deployment The current instance for chaining

    .. php:method:: setDeploymentBasePath($deploymentConfigurationPath)

        Set the deployment base path

        :type $deploymentConfigurationPath: string
        :param $deploymentConfigurationPath:

    .. php:method:: getDeploymentBasePath()

        Get the deployment base path (defaults to ./.surf)

        :returns: string

    .. php:method:: setWorkspacesBasePath($workspacesBasePath)

        :type $workspacesBasePath: string
        :param $workspacesBasePath:

    .. php:method:: setTemporaryPath($temporaryPath)

        :type $temporaryPath: string
        :param $temporaryPath:

    .. php:method:: getDeploymentConfigurationPath()

        Get the deployment configuration path (defaults to
        Build/Surf/DeploymentName/Configuration)

        :returns: string The path without a trailing slash

    .. php:method:: getWorkspacePath(Application $application)

        Get a local workspace directory for the application

        :type $application: Application
        :param $application:
        :returns: string

    .. php:method:: getTemporaryPath()

        Get path to a temp folder on the filesystem

    .. php:method:: setForceRun($force)

        :type $force: bool
        :param $force:

    .. php:method:: getForceRun()

        :returns: bool

    .. php:method:: getDeploymentLockIdentifier()

        :returns: string

    .. php:method:: setDeploymentLockIdentifier($deploymentLockIdentifier = null)

        :type $deploymentLockIdentifier: string|null
        :param $deploymentLockIdentifier:
