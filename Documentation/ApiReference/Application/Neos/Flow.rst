------------------------------------
TYPO3\\Surf\\Application\\Neos\\Flow
------------------------------------

.. php:namespace: TYPO3\\Surf\\Application\\Neos

.. php:class:: Flow

    A Neos Flow application template

    .. php:const:: DEFAULT_SHARED_DIR

        default directory name for shared directory

    .. php:attr:: context

        protected string

        The production context

    .. php:attr:: version

        protected string

        The Neos Flow major and minor version of this application

    .. php:attr:: symlinks

        protected array

        Symlinks, which should be created for each release.

    .. php:attr:: directories

        protected array

        Directories which should be created on deployment. E.g. shared folders.

    .. php:attr:: options

        protected array

        Basic application specific options

        packageMethod: How to prepare the application assets (code and files)
        locally before transfer

        "git" Make a local git checkout and transfer files to the server none
        Default, do not prepare anything locally

        transferMethod: How to transfer the application assets to a node

        "git" Make a checkout of the application assets remotely on the node

        updateMethod: How to prepare and update the application assets on the node
        after transfer

        lockDeployment: Locked deployments can only run once at a time

    .. php:attr:: name

        protected string

        The name

    .. php:attr:: nodes

        protected array

        The nodes for this application

    .. php:attr:: deploymentPath

        protected string

        The deployment path for this application on a node

    .. php:attr:: releasesDirectory

        protected string

        The relative releases directory for this application on a node

    .. php:method:: __construct($name = 'Neos Flow')

        Constructor

        :type $name: string
        :param $name:

    .. php:method:: registerTasks(Workflow $workflow, Deployment $deployment)

        Register tasks for this application

        :type $workflow: Workflow
        :param $workflow:
        :type $deployment: Deployment
        :param $deployment:

    .. php:method:: registerTasksForUpdateMethod(Workflow $workflow, $updateMethod)

        Add support for updateMethod "composer"

        :type $workflow: Workflow
        :param $workflow:
        :type $updateMethod: string
        :param $updateMethod:

    .. php:method:: setContext($context)

        Set the application production context

        :type $context: string
        :param $context:
        :returns: Flow

    .. php:method:: getContext()

        Get the application production context

        :returns: string

    .. php:method:: setVersion($version)

        :type $version: string
        :param $version:

    .. php:method:: getVersion()

        :returns: string

    .. php:method:: getBuildEssentialsDirectoryName()

        Get the directory name for build essentials (e.g. to run unit tests)

        The value depends on the Flow version of the application.

        :returns: string

    .. php:method:: getFlowScriptName()

        Get the name of the Flow script (flow or flow3)

        The value depends on the Flow version of the application.

        :returns: string

    .. php:method:: getCommandPackageKey($command = '')

        Get the package key to prefix the command

        :type $command: string
        :param $command:
        :returns: string

    .. php:method:: buildCommand($targetPath, $command, $arguments = [], $phpBinaryPathAndFilename = 'php')

        Returns a executable flow command including the context

        :type $targetPath: string
        :param $targetPath: the path where the command should be executed
        :type $command: string
        :param $command: the actual command for example `cache:flush`
        :type $arguments: array
        :param $arguments: list of arguments which will be appended to the command
        :type $phpBinaryPathAndFilename: string
        :param $phpBinaryPathAndFilename: the path to the php binary
        :returns: string

    .. php:method:: setSymlinks($symlinks)

        Override all symlinks to be created with the given array of symlinks.

        :type $symlinks: array
        :param $symlinks:
        :returns: \TYPO3\Surf\Application\BaseApplication

    .. php:method:: getSymlinks()

        Get all symlinks to be created for the application

        :returns: array

    .. php:method:: addSymlink($linkPath, $sourcePath)

        Register an additional symlink to be created for the application

        :type $linkPath: string
        :param $linkPath: The link to create
        :type $sourcePath: string
        :param $sourcePath: The file/directory where the link should point to
        :returns: \TYPO3\Surf\Application\BaseApplication

    .. php:method:: addSymlinks($symlinks)

        Register an array of additional symlinks to be created for the application

        :type $symlinks: array
        :param $symlinks:
        :returns: \TYPO3\Surf\Application\BaseApplication

    .. php:method:: setDirectories($directories)

        Override all directories to be created for the application

        :type $directories: array
        :param $directories:
        :returns: \TYPO3\Surf\Application\BaseApplication

    .. php:method:: getDirectories()

        Get directories to be created for the application

        :returns: array

    .. php:method:: addDirectory($path)

        Register an additional directory to be created for the application

        :type $path: string
        :param $path:
        :returns: \TYPO3\Surf\Application\BaseApplication

    .. php:method:: addDirectories($directories)

        Register an array of additional directories to be created for the
        application

        :type $directories: array
        :param $directories:
        :returns: \TYPO3\Surf\Application\BaseApplication

    .. php:method:: registerTasksForPackageMethod(Workflow $workflow, $packageMethod)

        :type $workflow: Workflow
        :param $workflow:
        :type $packageMethod: string
        :param $packageMethod:

    .. php:method:: registerTasksForTransferMethod(Workflow $workflow, $transferMethod)

        :type $workflow: Workflow
        :param $workflow:
        :type $transferMethod: string
        :param $transferMethod:

    .. php:method:: getName()

        Get the application name

        :returns: string

    .. php:method:: setName($name)

        Sets the application name

        :type $name: string
        :param $name:
        :returns: \TYPO3\Surf\Domain\Model\Application The current instance for chaining

    .. php:method:: getNodes()

        Get the nodes where this application should be deployed

        :returns: Node[] The application nodes

    .. php:method:: setNodes($nodes)

        Set the nodes where this application should be deployed

        :type $nodes: array
        :param $nodes: The application nodes
        :returns: \TYPO3\Surf\Domain\Model\Application The current instance for chaining

    .. php:method:: addNode(Node $node)

        Add a node where this application should be deployed

        :type $node: Node
        :param $node: The node to add
        :returns: \TYPO3\Surf\Domain\Model\Application The current instance for chaining

    .. php:method:: hasNode(Node $node)

        Return TRUE if the given node is registered for this application

        :type $node: Node
        :param $node: The node to test
        :returns: bool TRUE if the node is registered for this application

    .. php:method:: getDeploymentPath()

        Get the deployment path for this application

        This is the path for an application pointing to the root of the Surf
        deployment:

        [deploymentPath]
        |-- $this->getReleasesDirectory()
        |-- cache
        |-- shared

        :returns: string The deployment path

    .. php:method:: getSharedPath()

        Get the path for shared resources for this application

        This path defaults to a directory "shared" below the deployment path.

        :returns: string The shared resources path

    .. php:method:: getSharedDirectory()

        Returns the shared directory

        takes directory name from option "sharedDirectory"
        if option is not set or empty constant DEFAULT_SHARED_DIR "shared" is used

        :returns: string

    .. php:method:: setDeploymentPath($deploymentPath)

        Sets the deployment path

        :type $deploymentPath: string
        :param $deploymentPath: The deployment path
        :returns: \TYPO3\Surf\Domain\Model\Application The current instance for chaining

    .. php:method:: getReleasesDirectory()

        Returns the releases directory

        :returns: string $releasesDirectory

    .. php:method:: setReleasesDirectory($releasesDirectory)

        Sets the releases directory

        :type $releasesDirectory: string
        :param $releasesDirectory:
        :returns: \TYPO3\Surf\Domain\Model\Application The current instance for chaining

    .. php:method:: getReleasesPath()

        Returns path to the directory with releases

        :returns: string Path to the releases directory

    .. php:method:: getOptions()

        Get all options defined on this application instance

        The options will include the deploymentPath and sharedPath for unified
        option handling.

        :returns: array An array of options indexed by option key

    .. php:method:: getOption($key)

        Get an option defined on this application instance

        :type $key: string
        :param $key:
        :returns: mixed

    .. php:method:: hasOption($key)

        Test if an option was set for this application

        :type $key: string
        :param $key: The option key
        :returns: bool TRUE If the option was set

    .. php:method:: setOptions($options)

        Sets all options for this application instance

        :type $options: array
        :param $options: The options to set indexed by option key
        :returns: \TYPO3\Surf\Domain\Model\Application The current instance for chaining

    .. php:method:: setOption($key, $value)

        Set an option for this application instance

        :type $key: string
        :param $key: The option key
        :type $value: mixed
        :param $value: The option value
        :returns: \TYPO3\Surf\Domain\Model\Application The current instance for chaining
