---------------------------------------
TYPO3\\Surf\\Domain\\Model\\Application
---------------------------------------

.. php:namespace: TYPO3\\Surf\\Domain\\Model

.. php:class:: Application

    A generic application without any tasks

    .. php:const:: DEFAULT_SHARED_DIR

        default directory name for shared directory

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

    .. php:attr:: options

        protected array

        The options

    .. php:method:: __construct($name)

        Constructor

        :type $name: string
        :param $name:

    .. php:method:: registerTasks(Workflow $workflow, Deployment $deployment)

        Register tasks for this application

        This is a template method that should be overridden by specific
        applications to define new task or to add tasks to the workflow.

        Example:

        $workflow->addTask(CreateDirectoriesTask::class, 'initialize', $this);

        :type $workflow: Workflow
        :param $workflow:
        :type $deployment: Deployment
        :param $deployment:

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
