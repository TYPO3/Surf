<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Model;

use InvalidArgumentException;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use Webmozart\Assert\Assert;

/**
 * A generic application without any tasks
 */
class Application
{
    /**
     * @var string
     */
    public const DEFAULT_SHARED_DIR = 'shared';

    /**
     * @var string
     */
    public const DEFAULT_WEB_DIRECTORY = 'public';

    protected string $name;

    /**
     * The nodes for this application
     * @var Node[]
     */
    protected array $nodes = [];

    /**
     * The deployment path for this application on a node
     */
    protected string $deploymentPath = '';

    /**
     * The relative releases directory for this application on a node
     */
    protected string $releasesDirectory = 'releases';

    /**
     * @var array<string,mixed>
     */
    protected array $options = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Register tasks for this application
     *
     * This is a template method that should be overridden by specific applications to define
     * new task or to add tasks to the workflow.
     *
     * Example:
     *
     *   $workflow->addTask(CreateDirectoriesTask::class, SimpleWorkflowStage::STEP_01_INITIALIZE, $this);
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment): void
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the nodes where this application should be deployed
     *
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Set the nodes where this application should be deployed
     *
     * @param Node[] $nodes The application nodes
     */
    public function setNodes(array $nodes): self
    {
        Assert::allIsInstanceOf($nodes, Node::class);

        $this->nodes = $nodes;

        // If deployment path is set in application, set it for all nodes where path is not set
        array_map(function (Node $node) {
            if ($node->getDeploymentPath() === '') {
                $node->setDeploymentPath($this->deploymentPath);
            }
        }, $this->nodes);

        return $this;
    }

    public function addNode(Node $node): self
    {
        $this->nodes[$node->getName()] = $node;

        // If deployment path is set in application, set it for all nodes where path is not set
        if ($node->getDeploymentPath() === '') {
            $node->setDeploymentPath($this->deploymentPath);
        }

        return $this;
    }

    public function hasNode(Node $node): bool
    {
        return isset($this->nodes[$node->getName()]);
    }

    /**
     * Get the deployment path for this application
     *
     * This is the path for an application pointing to the root of the Surf deployment:
     *
     * [deploymentPath]
     * |-- $this->getReleasesDirectory()
     * |-- cache
     * |-- shared
     */
    public function getDeploymentPath(): string
    {
        return $this->deploymentPath;
    }

    /**
     * Get the path for shared resources for this application
     *
     * This path defaults to a directory "shared" below the deployment path.
     */
    public function getSharedPath(): string
    {
        return $this->getDeploymentPath() . '/' . $this->getSharedDirectory();
    }

    /**
     * Returns the shared directory
     *
     * takes directory name from option "sharedDirectory"
     * if option is not set or empty constant DEFAULT_SHARED_DIR "shared" is used
     */
    public function getSharedDirectory(): string
    {
        $sharedDirectory = $this->provideStringOption('sharedDirectory');
        if ($sharedDirectory === '') {
            return self::DEFAULT_SHARED_DIR;
        }

        $message = sprintf(
            'Relative constructs as "../" are not allowed in option "sharedDirectory". Given option: "%s"',
            $sharedDirectory
        );

        $this->ensureThatNoRelativePathIsGiven($sharedDirectory, $message);

        return rtrim($sharedDirectory, '/');
    }

    public function setDeploymentPath(string $deploymentPath): self
    {
        $this->deploymentPath = rtrim($deploymentPath, '/');

        // If deployment path is set in application, set it for all nodes where path is not set
        array_map(function (Node $node) {
            if ($node->getDeploymentPath() === '') {
                $node->setDeploymentPath($this->deploymentPath);
            }
        }, $this->nodes);

        return $this;
    }

    public function getReleasesDirectory(): string
    {
        return $this->releasesDirectory;
    }

    public function setReleasesDirectory(string $releasesDirectory): self
    {
        $message = sprintf('"../" is not allowed in the releases directory "%s"', $releasesDirectory);

        $this->ensureThatNoRelativePathIsGiven($releasesDirectory, $message);
        $this->releasesDirectory = trim($releasesDirectory, '/');

        return $this;
    }

    /**
     * Returns path to the directory with releases
     */
    public function getReleasesPath(): string
    {
        return rtrim($this->getDeploymentPath() . '/' . $this->getReleasesDirectory(), '/');
    }

    /**
     * Get all options defined on this application instance
     *
     * The options will include the deploymentPath and sharedPath for
     * unified option handling.
     *
     * @return array<string,mixed>
     */
    public function getOptions(): array
    {
        return array_merge($this->options, [
            'deploymentPath' => $this->getDeploymentPath(),
            'releasesPath' => $this->getReleasesPath(),
            'sharedPath' => $this->getSharedPath(),
        ]);
    }

    /**
     * Get an option defined on this application instance
     *
     * @return mixed
     */
    public function getOption(string $key)
    {
        switch ($key) {
            case 'deploymentPath':
                return $this->getDeploymentPath();
            case 'releasesPath':
                return $this->getReleasesPath();
            case 'sharedPath':
                return $this->getSharedPath();
            default:
                return $this->options[$key];
        }
    }

    public function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    public function provideBoolOption(string $key): bool
    {
        return $this->options[$key] ?? false;
    }

    public function provideStringOption(string $key): string
    {
        if (! $this->hasOption($key)) {
            return '';
        }

        return (string)$this->getOption($key);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param string $key The option key
     * @param mixed $value The option value
     */
    public function setOption(string $key, $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }

    private function ensureThatNoRelativePathIsGiven(string $directory, string $message): void
    {
        try {
            Assert::notRegex($directory, '/(^|\/)\.\.(\/|$)/', $message);
        } catch (InvalidArgumentException $e) {
            throw new InvalidConfigurationException(
                $e->getMessage(),
                1490107183141
            );
        }
    }
}
