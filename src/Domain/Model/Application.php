<?php

namespace TYPO3\Surf\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A generic application without any tasks.
 */
class Application
{
    /**
     * default directory name for shared directory.
     *
     * @const
     */
    public const DEFAULT_SHARED_DIR = 'shared';

    public const DEFAULT_WEB_DIRECTORY = 'public';

    /**
     * The name.
     *
     * @var string
     */
    protected $name;

    /**
     * The nodes for this application.
     *
     * @var array
     */
    protected $nodes = [];

    /**
     * The deployment path for this application on a node.
     *
     * @var string
     */
    protected $deploymentPath;

    /**
     * The relative releases directory for this application on a node.
     *
     * @var string
     */
    protected $releasesDirectory = 'releases';

    /**
     * The options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Register tasks for this application.
     *
     * This is a template method that should be overridden by specific applications to define
     * new task or to add tasks to the workflow.
     *
     * Example:
     *
     *   $workflow->addTask(CreateDirectoriesTask::class, 'initialize', $this);
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment)
    {
    }

    /**
     * Get the application name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the application name.
     *
     * @param string $name
     *
     * @return Application The current instance for chaining
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the nodes where this application should be deployed.
     *
     * @return Node[] The application nodes
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * Set the nodes where this application should be deployed.
     *
     * @param array $nodes The application nodes
     *
     * @return Application The current instance for chaining
     */
    public function setNodes(array $nodes)
    {
        $this->nodes = $nodes;

        return $this;
    }

    /**
     * Add a node where this application should be deployed.
     *
     * @return Application The current instance for chaining
     */
    public function addNode(Node $node)
    {
        $this->nodes[$node->getName()] = $node;

        return $this;
    }

    /**
     * Return TRUE if the given node is registered for this application.
     *
     * @return bool TRUE if the node is registered for this application
     */
    public function hasNode(Node $node)
    {
        return isset($this->nodes[$node->getName()]);
    }

    /**
     * Get the deployment path for this application.
     *
     * This is the path for an application pointing to the root of the Surf deployment:
     *
     * [deploymentPath]
     * |-- $this->getReleasesDirectory()
     * |-- cache
     * |-- shared
     *
     * @return string The deployment path
     */
    public function getDeploymentPath()
    {
        return $this->deploymentPath;
    }

    /**
     * Get the path for shared resources for this application.
     *
     * This path defaults to a directory "shared" below the deployment path.
     *
     * @return string The shared resources path
     */
    public function getSharedPath()
    {
        return $this->getDeploymentPath().'/'.$this->getSharedDirectory();
    }

    /**
     * Returns the shared directory.
     *
     * takes directory name from option "sharedDirectory"
     * if option is not set or empty constant DEFAULT_SHARED_DIR "shared" is used
     *
     * @return string
     */
    public function getSharedDirectory()
    {
        $result = self::DEFAULT_SHARED_DIR;
        if ($this->hasOption('sharedDirectory') && !empty($this->getOption('sharedDirectory'))) {
            $sharedPath = $this->getOption('sharedDirectory');
            if (preg_match('/(^|\/)\.\.(\/|$)/', $sharedPath)) {
                throw new InvalidConfigurationException(
                    sprintf(
                        'Relative constructs as "../" are not allowed in option "sharedDirectory". Given option: "%s"',
                        $sharedPath
                    ),
                    1490107183141
                );
            }
            $result = rtrim($sharedPath, '/');
        }

        return $result;
    }

    /**
     * Sets the deployment path.
     *
     * @param string $deploymentPath The deployment path
     *
     * @return Application The current instance for chaining
     */
    public function setDeploymentPath($deploymentPath)
    {
        $this->deploymentPath = rtrim($deploymentPath, '/');

        return $this;
    }

    /**
     * Returns the releases directory.
     *
     * @return string $releasesDirectory
     */
    public function getReleasesDirectory()
    {
        return $this->releasesDirectory;
    }

    /**
     * Sets the releases directory.
     *
     * @param string $releasesDirectory
     *
     * @return Application The current instance for chaining
     */
    public function setReleasesDirectory($releasesDirectory)
    {
        if (preg_match('/(^|\/)\.\.(\/|$)/', $releasesDirectory)) {
            throw new InvalidConfigurationException(
                sprintf('"../" is not allowed in the releases directory "%s"', $releasesDirectory),
                1380870750
            );
        }
        $this->releasesDirectory = trim($releasesDirectory, '/');

        return $this;
    }

    /**
     * Returns path to the directory with releases.
     *
     * @return string Path to the releases directory
     */
    public function getReleasesPath()
    {
        return rtrim($this->getDeploymentPath().'/'.$this->getReleasesDirectory(), '/');
    }

    /**
     * Get all options defined on this application instance.
     *
     * The options will include the deploymentPath and sharedPath for
     * unified option handling.
     *
     * @return array An array of options indexed by option key
     */
    public function getOptions()
    {
        return array_merge($this->options, [
            'deploymentPath' => $this->getDeploymentPath(),
            'releasesPath'   => $this->getReleasesPath(),
            'sharedPath'     => $this->getSharedPath(),
        ]);
    }

    /**
     * Get an option defined on this application instance.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getOption($key)
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

    /**
     * Test if an option was set for this application.
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
     * Sets all options for this application instance.
     *
     * @param array $options The options to set indexed by option key
     *
     * @return Application The current instance for chaining
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set an option for this application instance.
     *
     * @param string $key   The option key
     * @param mixed  $value The option value
     *
     * @return Application The current instance for chaining
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }
}
