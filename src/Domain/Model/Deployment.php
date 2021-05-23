<?php

namespace TYPO3\Surf\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Exception as SurfException;

/**
 * A Deployment
 *
 * This is the base object exposed to a deployment configuration script and serves as a configuration builder and
 * model for an actual deployment.
 */
class Deployment implements LoggerAwareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const STATUS_SUCCESS = 0;
    public const STATUS_FAILED = 1;
    public const STATUS_CANCELLED = 2;
    public const STATUS_UNKNOWN = 3;

    /**
     * The name of this deployment
     * @var string
     */
    protected $name;

    /**
     * The workflow used for this deployment
     * @var Workflow
     */
    protected $workflow;

    /**
     * The applications deployed with this deployment
     * @var Application[]
     */
    protected $applications = [];

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
    protected $initCallbacks = [];

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
    protected $options = [];

    /**
     * The deployment declaration base path for this deployment
     * @var string
     */
    protected $deploymentBasePath;

    /**
     * The base path to the local workspaces when packaging for deployment
     * (may be temporary directory)
     *
     * @var string
     */
    protected $workspacesBasePath;

    /**
     * The relative base path to the project root (for example 'htdocs')
     *
     * @var string
     */
    protected $relativeProjectRootPath = '';

    /**
     * The base path to a temporary directory
     *
     * @var string
     */
    protected $temporaryPath;

    /**
     * @var bool
     */
    private $forceRun = false;

    /**
     * @var string
     */
    private $deploymentLockIdentifier;

    public function __construct($name, $deploymentLockIdentifier = null)
    {
        $this->name = $name;
        $this->releaseIdentifier = strftime('%Y%m%d%H%M%S', time());

        $this->setDeploymentLockIdentifier($deploymentLockIdentifier);
    }

    /**
     * Initialize the deployment
     *
     * Must be called before calling deploy() on a deployment.
     *
     * A time-based release identifier will be created on initialization. It also executes
     * callbacks given to the deployment with onInitialize(...).
     *
     * @throws SurfException
     */
    public function initialize()
    {
        if ($this->initialized) {
            throw new SurfException('Already initialized', 1335976472);
        }
        if ($this->workflow === null) {
            $this->workflow = $this->container->get(SimpleWorkflow::class);
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
     * @param callable $callback
     *
     * @return Deployment
     */
    public function onInitialize($callback)
    {
        $this->initCallbacks[] = $callback;

        return $this;
    }

    /**
     * Run this deployment
     *
     * @throws SurfException
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
     */
    public function simulate()
    {
        $this->setDryRun(true);
        $this->logger->notice('Simulating ' . $this->name . ' (' . $this->releaseIdentifier . ')');
        $this->workflow->run($this);
    }

    /**
     * @param Node $node
     * @return string
     */
    public function getApplicationReleaseBasePath(Node $node)
    {
        return Files::concatenatePaths([
            $node->getReleasesPath(),
            $this->getReleaseIdentifier()
        ]);
    }

    /**
     * @param Node $node
     * @return string
     */
    public function getApplicationReleasePath(Node $node)
    {
        return Files::concatenatePaths([
            $this->getApplicationReleaseBasePath($node),
            $this->relativeProjectRootPath
        ]);
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
     *
     * @return Deployment The current deployment instance for chaining
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get all nodes of this deployment
     *
     * @return Node[] The deployment nodes with all application nodes
     */
    public function getNodes()
    {
        $nodes = [];
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
     * @return Node The Node or NULL if no Node with the given name was found
     */
    public function getNode($name)
    {
        if ($name === 'localhost') {
            $node = new Node('localhost');
            $node->onLocalhost();
            return $node;
        }
        $nodes = $this->getNodes();

        return $nodes[$name] ?? null;
    }

    /**
     * Get all applications for this deployment
     *
     * @return Application[]
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Add an application to this deployment
     *
     * @param Application $application The application to add
     *
     * @return Deployment The current deployment instance for chaining
     */
    public function addApplication(Application $application)
    {
        $this->applications[$application->getName()] = $application;

        return $this;
    }

    /**
     * Get the deployment workflow
     *
     * @return Workflow The deployment workflow
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * Sets the deployment workflow
     *
     * @param Workflow $workflow The workflow to set
     *
     * @return Deployment The current deployment instance for chaining
     */
    public function setWorkflow($workflow)
    {
        $this->workflow = $workflow;

        return $this;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return Deployment
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
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

    public function setRelativeProjectRootPath($relativeProjectRootPath)
    {
        $this->relativeProjectRootPath = $relativeProjectRootPath;

        return $this;
    }

    public function getRelativeProjectRootPath()
    {
        return $this->relativeProjectRootPath;
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
     *
     * @return Deployment The current deployment instance for chaining
     */
    public function setDryRun($dryRun)
    {
        $this->dryRun = $dryRun;

        return $this;
    }

    /**
     * @param int $status
     *
     * @return Deployment
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
     *
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
     *
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
     *
     * @return Deployment The current instance for chaining
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
     *
     * @return Deployment The current instance for chaining
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
     * @param string $temporaryPath
     */
    public function setTemporaryPath($temporaryPath)
    {
        $this->temporaryPath = rtrim($temporaryPath, '\\/');
    }

    /**
     * Get the deployment configuration path (defaults to ./.surf/DeploymentName/Configuration)
     *
     * @return string The path without a trailing slash
     */
    public function getDeploymentConfigurationPath()
    {
        return Files::concatenatePaths([
            $this->getDeploymentBasePath(),
            $this->getName(),
            'Configuration'
        ]);
    }

    /**
     * Get a local workspace directory for the application
     *
     * @param Application $application
     *
     * @return string
     */
    public function getWorkspacePath(Application $application)
    {
        return Files::concatenatePaths([
            $this->workspacesBasePath,
            $this->getName(),
            $application->getName()
        ]);
    }

    /**
     * Get a local workspace directory for the application
     *
     * @param Application $application
     *
     * @return string
     */
    public function getWorkspaceWithProjectRootPath(Application $application)
    {
        return Files::concatenatePaths([
            $this->getWorkspacePath($application),
            $this->relativeProjectRootPath
        ]);
    }

    /**
     * Get path to a temp folder on the filesystem
     */
    public function getTemporaryPath()
    {
        return $this->temporaryPath;
    }

    public function rollback(bool $dryRun = false)
    {
        $this->logger->notice('Rollback deployment ' . $this->name . ' (' . $this->releaseIdentifier . ')');

        $this->setWorkflow($this->container->get(RollbackWorkflow::class));
        $this->initialize();
        if ($dryRun) {
            $this->setDryRun(true);
        }
        $this->workflow->run($this);
    }

    /**
     * @param bool $force
     */
    public function setForceRun($force)
    {
        $this->forceRun = (bool)$force;
    }

    /**
     * @return bool
     */
    public function getForceRun()
    {
        return $this->forceRun;
    }

    /**
     * @return string
     */
    public function getDeploymentLockIdentifier()
    {
        return $this->deploymentLockIdentifier;
    }

    /**
     * @param string|null $deploymentLockIdentifier
     */
    private function setDeploymentLockIdentifier($deploymentLockIdentifier = null)
    {
        if (! is_string($deploymentLockIdentifier) || $deploymentLockIdentifier === '') {
            $deploymentLockIdentifier = getenv('SURF_DEPLOYMENT_LOCK_IDENTIFIER') !== false
                ? (string)getenv('SURF_DEPLOYMENT_LOCK_IDENTIFIER')
                : $this->releaseIdentifier;
        }
        $this->deploymentLockIdentifier = $deploymentLockIdentifier;
    }
}
