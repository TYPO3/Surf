<?php
namespace TYPO3\Surf\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".            *
 *                                                                        *
 *                                                                        */

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * A Deployment
 *
 * This is the base object exposed to a deployment configuration script and serves as a configuration builder and
 * model for an actual deployment.
 */
class Deployment implements LoggerAwareInterface
{
    const STATUS_SUCCESS = 0;
    const STATUS_FAILED = 1;
    const STATUS_CANCELLED = 2;
    const STATUS_UNKNOWN = 3;

    /**
     * The name of this deployment
     * @var string
     */
    protected $name;

    /**
     * The workflow used for this deployment
     * @var \TYPO3\Surf\Domain\Model\Workflow
     */
    protected $workflow;

    /**
     * The applications deployed with this deployment
     * @var array
     */
    protected $applications = array();

    /**
     * A logger instance used to log messages during deployment
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * The release identifier will be created on each deployment
     * @var string
     */
    protected $releaseIdentifier;

    /**
     * TRUE if the deployment should be simulated
     * @var string
     */
    protected $dryRun = false;

    /**
     * Callbacks that should be executed after initialization
     * @var array
     */
    protected $initCallbacks = array();

    /**
     * Tells if the deployment ran successfully or failed
     * @var int
     */
    protected $status = self::STATUS_UNKNOWN;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * The options
     * @var array
     */
    protected $options = array();

    /**
     * The deployment declaration base path for this deployment
     * @var string
     */
    protected $deploymentBasePath;

    /**
     * The base path of to the local workspaces when packaging for deployment
     * (may be temporary directory)
     *
     * @var string
     */
    protected $workspacesBasePath;

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->releaseIdentifier = strftime('%Y%m%d%H%M%S', time());
    }

    /**
     * Initialize the deployment
     *
     * Must be called before calling deploy() on a deployment.
     *
     * A time-based release identifier will be created on initialization. It also executes
     * callbacks given to the deployment with onInitialize(...).
     *
     * @return void
     * @throws \TYPO3\Surf\Exception
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function initialize()
    {
        if ($this->initialized) {
            throw new \TYPO3\Surf\Exception('Already initialized', 1335976472);
        }
        if ($this->workflow === null) {
            $this->workflow = new SimpleWorkflow();
        }

        foreach ($this->applications as $application) {
            $application->registerTasks($this->workflow, $this);
        }
        foreach ($this->initCallbacks as $callback) {
            $callback();
        }

        $this->initialized = true;
    }

    /**
     * Add a callback to the initialization
     *
     * @param callback $callback
     * @return \TYPO3\Surf\Domain\Model\Deployment
     */
    public function onInitialize($callback)
    {
        $this->initCallbacks[] = $callback;
        return $this;
    }

    /**
     * Run this deployment
     *
     * @return void
     */
    public function deploy()
    {
        $this->logger->notice('Deploying ' . $this->name . ' (' . $this->releaseIdentifier . ')');
        $this->workflow->run($this);
    }

    /**
     * Simulate this deployment without executing tasks
     *
     * It will set dryRun = TRUE which can be inspected by any task.
     *
     * @return void
     */
    public function simulate()
    {
        $this->setDryRun(true);
        $this->logger->notice('Simulating ' . $this->name . ' (' . $this->releaseIdentifier . ')');
        $this->workflow->run($this);
    }

    /**
     *
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @return string
     */
    public function getApplicationReleasePath(Application $application)
    {
        return $application->getReleasesPath() . '/' . $this->getReleaseIdentifier();
    }

    /**
     * Get the Deployment's name
     *
     * @return string The Deployment's name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the deployment name
     *
     * @param string $name The deployment name
     * @return \TYPO3\Surf\Domain\Model\Deployment The current deployment instance for chaining
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get all nodes of this deployment
     *
     * @return array The deployment nodes with all application nodes
     */
    public function getNodes()
    {
        $nodes = array();
        foreach ($this->applications as $application) {
            foreach ($application->getNodes() as $node) {
                $nodes[$node->getName()] = $node;
            }
        }
        return $nodes;
    }

    /**
     * Get a node by name
     *
     * In the special case "localhost" an ad-hoc Node with hostname "localhost" is returned.
     *
     * @return \TYPO3\Surf\Domain\Model\Node The Node or NULL if no Node with the given name was found
     */
    public function getNode($name)
    {
        if ($name === 'localhost') {
            $node = new Node('localhost');
            $node->setHostname('localhost');
            return $node;
        }
        $nodes = $this->getNodes();
        return isset($nodes[$name]) ? $nodes[$name] : null;
    }

    /**
     * Get all applications for this deployment
     *
     * @return array
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Add an application to this deployment
     *
     * @param \TYPO3\Surf\Domain\Model\Application $application The application to add
     * @return \TYPO3\Surf\Domain\Model\Deployment The current deployment instance for chaining
     */
    public function addApplication(Application $application)
    {
        $this->applications[$application->getName()] = $application;
        return $this;
    }

    /**
     * Get the deployment workflow
     *
     * @return \TYPO3\Surf\Domain\Model\Workflow The deployment workflow
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * Sets the deployment workflow
     *
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow The workflow to set
     * @return \TYPO3\Surf\Domain\Model\Deployment The current deployment instance for chaining
     */
    public function setWorkflow($workflow)
    {
        $this->workflow = $workflow;
        return $this;
    }

    /**
     *
     * @param LoggerInterface $logger
     * @return \TYPO3\Surf\Domain\Model\Deployment
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Get the deployment release identifier
     *
     * This gets the current release identifier when running a deployment.
     *
     * @return string The release identifier
     */
    public function getReleaseIdentifier()
    {
        return $this->releaseIdentifier;
    }

    /**
     * @return bool TRUE If the deployment is run in "dry run" mode
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    /**
     * Set the dry run mode for this deployment
     *
     * @param bool $dryRun
     * @return \TYPO3\Surf\Domain\Model\Deployment The current deployment instance for chaining
     */
    public function setDryRun($dryRun)
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    /**
     * @param int $status
     * @return \TYPO3\Surf\Domain\Model\Deployment
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get the current deployment status
     *
     * @return int One of the Deployment::STATUS_* constants
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool TRUE If the deployment is initialized
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * Get all options defined on this application instance
     *
     * The options will include the deploymentPath and sharedPath for
     * unified option handling.
     *
     * @return array An array of options indexed by option key
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get an option defined on the deployment
     *
     * @param string $key
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->options[$key];
    }

    /**
     * Test if an option was set for this deployment
     *
     * @param string $key The option key
     * @return bool TRUE If the option was set
     */
    public function hasOption($key)
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Sets all options for the deployment
     *
     * @param array $options The options to set indexed by option key
     * @return \TYPO3\Surf\Domain\Model\Deployment The current instance for chaining
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Set an option for the deployment
     *
     * @param string $key The option key
     * @param mixed $value The option value
     * @return \TYPO3\Surf\Domain\Model\Deployment The current instance for chaining
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Set the deployment base path
     *
     * @param string $deploymentConfigurationPath
     */
    public function setDeploymentBasePath($deploymentConfigurationPath)
    {
        $this->deploymentBasePath = $deploymentConfigurationPath;
    }

    /**
     * Get the deployment base path (defaults to ./.surf)
     *
     * @return string
     */
    public function getDeploymentBasePath()
    {
        return $this->deploymentBasePath;
    }

    /**
     * @param string $workspacesBasePath
     */
    public function setWorkspacesBasePath($workspacesBasePath)
    {
        $this->workspacesBasePath = rtrim($workspacesBasePath, '\\/');
    }

    /**
     * Get the deployment configuration path (defaults to Build/Surf/DeploymentName/Configuration)
     *
     * @return string The path without a trailing slash
     */
    public function getDeploymentConfigurationPath()
    {
        return $this->getDeploymentBasePath() . '/' . $this->getName() . '/Configuration';
    }

    /**
     * Get a local workspace directory for the application
     */
    public function getWorkspacePath(Application $application)
    {
        return  $this->workspacesBasePath . '/' . $this->getName() . '/' . $application->getName();
    }
}
