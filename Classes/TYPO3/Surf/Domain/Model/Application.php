<?php
namespace TYPO3\Surf\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A generic application without any tasks
 *
 */
class Application {

	/**
	 * The name
	 * @var string
	 */
	protected $name;

	/**
	 * The nodes for this application
	 * @var array
	 */
	protected $nodes = array();

	/**
	 * The deployment path for this application on a node
	 * @var string
	 */
	protected $deploymentPath;

	/**
	 * The options
	 * @var array
	 */
	protected $options = array();

	/**
	 * Constructor
	 *
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * Register tasks for this application
	 *
	 * This is a template method that should be overriden by specific applications to define
	 * new task or to add tasks to the workflow.
	 *
	 * Example:
	 *
	 *   $workflow->addTask('typo3.surf:createdirectories', 'initialize', $this);
	 *
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function registerTasks(Workflow $workflow, Deployment $deployment) {}

	/**
	 * Get the application name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the application name
	 *
	 * @param string $name
	 * @return \TYPO3\Surf\Domain\Model\Application The current instance for chaining
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get the nodes where this application should be deployed
	 *
	 * @return array The application nodes
	 */
	public function getNodes() {
		return $this->nodes;
	}

	/**
	 * Set the nodes where this application should be deployed
	 *
	 * @param array $nodes The application nodes
	 * @return \TYPO3\Surf\Domain\Model\Application The current instance for chaining
	 */
	public function setNodes(array $nodes) {
		$this->nodes = $nodes;
		return $this;
	}

	/**
	 * Add a node where this application should be deployed
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node The node to add
	 * @return \TYPO3\Surf\Domain\Model\Application The current instance for chaining
	 */
	public function addNode(Node $node) {
		$this->nodes[$node->getName()] = $node;
		return $this;
	}

	/**
	 * Return TRUE if the given node is registered for this application
	 *
	 * @param Node $node The node to test
	 * @return boolean TRUE if the node is registered for this application
	 */
	public function hasNode(Node $node) {
		return isset($this->nodes[$node->getName()]);
	}

	/**
	 * Get the deployment path for this application
	 *
	 * This is the path for an application pointing to the root of the Surf deployment:
	 *
	 * [deploymentPath]
	 * |-- releases
	 * |-- cache
	 * |-- shared
	 *
	 * @return string The deployment path
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException If no deployment path was set
	 */
	public function getDeploymentPath() {
		/*
		 * FIXME Move check somewhere else
		 *
		if ($this->deploymentPath === NULL) {
			throw new InvalidConfigurationException(sprintf('No deployment path has been defined for application %s.', $this->name), 1312220645);
		}
		*/
		return $this->deploymentPath;
	}

	/**
	 * Get the path for shared resources for this application
	 *
	 * This path defaults to a directory "shared" below the deployment path.
	 *
	 * @return string The shared resources path
	 */
	public function getSharedPath() {
		return $this->getDeploymentPath() . '/shared';
	}

	/**
	 * Sets the deployment path
	 *
	 * @param string $deploymentPath The deployment path
	 * @return \TYPO3\Surf\Domain\Model\Application The current instance for chaining
	 */
	public function setDeploymentPath($deploymentPath) {
		$this->deploymentPath = rtrim($deploymentPath, '/');
		return $this;
	}

	/**
	 * Get all options defined on this application instance
	 *
	 * The options will include the deploymentPath and sharedPath for
	 * unified option handling.
	 *
	 * @return array An array of options indexed by option key
	 */
	public function getOptions() {
		return array_merge($this->options, array(
			'deploymentPath' => $this->getDeploymentPath(),
			'sharedPath' => $this->getSharedPath()
		));
	}

	/**
	 * Get an option defined on this application instance
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getOption($key) {
		switch ($key) {
			case 'deploymentPath':
				return $this->deploymentPath;
			case 'sharedPath':
				return $this->getSharedPath();
			default:
				return $this->options[$key];
		}
	}

	/**
	 * Test if an option was set for this application
	 *
	 * @param string $key The option key
	 * @return boolean TRUE If the option was set
	 */
	public function hasOption($key) {
		return array_key_exists($key, $this->options);
	}

	/**
	 * Sets all options for this application instance
	 *
	 * @param array $options The options to set indexed by option key
	 * @return \TYPO3\Surf\Domain\Model\Application The current instance for chaining
	 */
	public function setOptions($options) {
		$this->options = $options;
		return $this;
	}

	/**
	 * Set an option for this application instance
	 *
	 * @param string $key The option key
	 * @param mixed $value The option value
	 * @return \TYPO3\Surf\Domain\Model\Application The current instance for chaining
	 */
	public function setOption($key, $value) {
		$this->options[$key] = $value;
		return $this;
	}

}
?>