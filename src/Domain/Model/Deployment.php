<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Model;

use Neos\Utility\Files;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use TYPO3\Surf\Domain\Enum\DeploymentStatus;
use TYPO3\Surf\Exception as SurfException;
use TYPO3\Surf\Integration\LoggerAwareTrait;
use UnexpectedValueException;

/**
 * This is the base object exposed to a deployment configuration script and serves as a configuration builder and
 * model for an actual deployment.
 */
class Deployment implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The name of this deployment
     */
    protected string $name;

    /**
     * The workflow used for this deployment
     */
    protected ?Workflow $workflow = null;

    /**
     * The applications deployed with this deployment
     * @var Application[]
     */
    protected array $applications = [];

    /**
     * A logger instance used to log messages during deployment
     */
    protected LoggerInterface $logger;

    /**
     * The release identifier will be created on each deployment
     */
    protected ?string $releaseIdentifier = null;

    /**
     * TRUE if the deployment should be simulated
     */
    protected bool $dryRun = false;

    /**
     * Callbacks that should be executed after initialization
     *
     * @var array<int,mixed>
     */
    protected array $initCallbacks = [];

    protected DeploymentStatus $status;

    protected bool $initialized = false;

    /**
     * @var array<string,mixed>
     */
    protected array $options = [];

    /**
     * The deployment declaration base path for this deployment
     */
    protected string $deploymentBasePath;

    /**
     * The base path to the local workspaces when packaging for deployment
     * (may be temporary directory)
     */
    protected string $workspacesBasePath;

    /**
     * The relative base path to the project root (for example 'htdocs')
     */
    protected string $relativeProjectRootPath = '';

    /**
     * The base path to a temporary directory
     */
    protected string $temporaryPath;

    private bool $forceRun = false;

    private string $deploymentLockIdentifier;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container, string $name, ?string $deploymentLockIdentifier = null)
    {
        $this->container = $container;
        $this->name = $name;
        $this->status = DeploymentStatus::UNKNOWN();
        $this->releaseIdentifier = date('YmdHis');

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
    public function initialize(): void
    {
        if ($this->initialized) {
            throw new SurfException('Already initialized', 1335976472);
        }
        if ($this->workflow === null) {
            $this->workflow = $this->createSimpleWorkflow();
        }

        foreach ($this->applications as $application) {
            $application->registerTasks($this->getWorkflow(), $this);
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
    public function onInitialize(callable $callback): self
    {
        $this->initCallbacks[] = $callback;

        return $this;
    }

    /**
     * Run this deployment
     *
     * @throws SurfException
     */
    public function deploy(): void
    {
        $this->logger->notice('Deploying ' . $this->name . ' (' . $this->releaseIdentifier . ')');
        $this->getWorkflow()->run($this);
    }

    /**
     * Simulate this deployment without executing tasks
     *
     * It will set dryRun = TRUE which can be inspected by any task.
     */
    public function simulate(): void
    {
        $this->setDryRun(true);
        $this->logger->notice('Simulating ' . $this->name . ' (' . $this->releaseIdentifier . ')');
        $this->getWorkflow()->run($this);
    }

    public function getApplicationReleaseBasePath(Node $node): string
    {
        return Files::concatenatePaths([
            $node->getReleasesPath(),
            $this->getReleaseIdentifier()
        ]);
    }

    public function getApplicationReleasePath(Node $node): string
    {
        return Files::concatenatePaths([
            $this->getApplicationReleaseBasePath($node),
            $this->relativeProjectRootPath
        ]);
    }

    /**
     * Get the Deployment's name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the deployment name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get all nodes of this deployment
     *
     * @return Node[] The deployment nodes with all application nodes
     */
    public function getNodes(): array
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
     * @return Node|null The Node or NULL if no Node with the given name was found
     */
    public function getNode(string $name): ?Node
    {
        if ($name === 'localhost') {
            return $this->createLocalhostNode();
        }
        $nodes = $this->getNodes();

        return $nodes[$name] ?? null;
    }

    public function createLocalhostNode(): Node
    {
        $node = new Node('localhost');
        $node->onLocalhost();
        return $node;
    }

    /**
     * Get all applications for this deployment
     *
     * @return Application[]
     */
    public function getApplications(): array
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
    public function addApplication(Application $application): self
    {
        $this->applications[$application->getName()] = $application;

        return $this;
    }

    public function getWorkflow(): Workflow
    {
        if (!$this->workflow instanceof Workflow) {
            throw new UnexpectedValueException('No workflow is defined for deployment');
        }

        return $this->workflow;
    }

    public function setWorkflow(Workflow $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }

    /**
     * Get the deployment release identifier
     *
     * This gets the current release identifier when running a deployment.
     */
    public function getReleaseIdentifier(): ?string
    {
        return $this->releaseIdentifier;
    }

    public function setRelativeProjectRootPath(string $relativeProjectRootPath): self
    {
        $this->relativeProjectRootPath = $relativeProjectRootPath;

        return $this;
    }

    public function getRelativeProjectRootPath(): string
    {
        return $this->relativeProjectRootPath;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function setDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;

        return $this;
    }

    public function setStatus(DeploymentStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): DeploymentStatus
    {
        return $this->status;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * Get all options defined on this application instance
     *
     * The options will include the deploymentPath and sharedPath for
     * unified option handling.
     *
     * @return array<string, mixed> An array of options indexed by option key
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return mixed
     */
    public function getOption(string $key)
    {
        return $this->options[$key];
    }

    public function hasOption(string $key): bool
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Sets all options for the deployment
     *
     * @param array<string, mixed> $options The options to set indexed by option key
     * @return Deployment The current instance for chaining
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set an option for the deployment
     *
     * @param mixed $value The option value
     */
    public function setOption(string $key, $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }

    public function setDeploymentBasePath(string $deploymentConfigurationPath): void
    {
        $this->deploymentBasePath = $deploymentConfigurationPath;
    }

    /**
     * Get the deployment base path (defaults to ./.surf)
     */
    public function getDeploymentBasePath(): string
    {
        return $this->deploymentBasePath;
    }

    public function setWorkspacesBasePath(string $workspacesBasePath): void
    {
        $this->workspacesBasePath = rtrim($workspacesBasePath, '\\/');
    }

    public function setTemporaryPath(string $temporaryPath): void
    {
        $this->temporaryPath = rtrim($temporaryPath, '\\/');
    }

    /**
     * Get the deployment configuration path (defaults to ./.surf/DeploymentName/Configuration)
     */
    public function getDeploymentConfigurationPath(): string
    {
        return Files::concatenatePaths([
            $this->getDeploymentBasePath(),
            $this->getName(),
            'Configuration'
        ]);
    }

    /**
     * Get a local workspace directory for the application
     */
    public function getWorkspacePath(Application $application): string
    {
        return Files::concatenatePaths([
            $this->workspacesBasePath,
            $this->getName(),
            $application->getName()
        ]);
    }

    /**
     * Get a local workspace directory for the application
     */
    public function getWorkspaceWithProjectRootPath(Application $application): string
    {
        return Files::concatenatePaths([
            $this->getWorkspacePath($application),
            $this->relativeProjectRootPath
        ]);
    }

    /**
     * Get path to a temp folder on the filesystem
     */
    public function getTemporaryPath(): string
    {
        return $this->temporaryPath;
    }

    public function rollback(bool $dryRun = false): void
    {
        $this->logger->notice('Rollback deployment ' . $this->name . ' (' . $this->releaseIdentifier . ')');

        /** @var RollbackWorkflow $workflow */
        $workflow = $this->container->get(RollbackWorkflow::class);
        $this->setWorkflow($workflow);
        $this->initialize();
        if ($dryRun) {
            $this->setDryRun(true);
        }
        $workflow->run($this);
    }

    public function setForceRun(bool $force): void
    {
        $this->forceRun = $force;
    }

    public function getForceRun(): bool
    {
        return $this->forceRun;
    }

    public function getDeploymentLockIdentifier(): string
    {
        return $this->deploymentLockIdentifier;
    }

    private function setDeploymentLockIdentifier(?string $deploymentLockIdentifier = null): void
    {
        if (! is_string($deploymentLockIdentifier) || $deploymentLockIdentifier === '') {
            $deploymentLockIdentifier = getenv('SURF_DEPLOYMENT_LOCK_IDENTIFIER') !== false
                ? (string)getenv('SURF_DEPLOYMENT_LOCK_IDENTIFIER')
                : $this->releaseIdentifier;
        }
        $this->deploymentLockIdentifier = (string)$deploymentLockIdentifier;
    }

    private function createSimpleWorkflow(): SimpleWorkflow
    {
        $workflow = $this->container->get(SimpleWorkflow::class);

        if (!$workflow instanceof SimpleWorkflow) {
            throw new UnexpectedValueException(sprintf('Workflow must be of type "%s"', SimpleWorkflow::class));
        }

        return $workflow;
    }

    public function provideBoolOption(string $key): bool
    {
        return $this->options[$key] ?? false;
    }
}
