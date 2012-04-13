<?php
namespace TYPO3\Surf\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

/**
 * A Node
 *
 */
class Node {

	/**
	 * The name
	 * @var string
	 */
	protected $name;

	/**
	 * The hostname
	 * @var string
	 */
	protected $hostname;

	/**
	 * Options for this node
	 *
	 * username: SSH username for connecting to this node (optional)
	 * port: SSH port for connecting to the node (optional)
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor
	 *
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * Get the Node's name
	 *
	 * @return string The Node's name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets this Node's name
	 *
	 * @param string $name The Node's name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get the Node's hostname
	 *
	 * @return string The Node's hostname
	 */
	public function getHostname() {
		return $this->hostname;
	}

	/**
	 * Sets this Node's hostname
	 *
	 * @param string $hostname The Node's hostname
	 * @return void
	 */
	public function setHostname($hostname) {
		$this->hostname = $hostname;
	}

	/**
	 * Get the Node's options
	 *
	 * @return array The Node's options
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Sets this Node's options
	 *
	 * @param array $options The Node's options
	 * @return void
	 */
	public function setOptions(array $options) {
		$this->options = $options;
	}

	/**
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getOption($key) {
		return $this->options[$key];
	}

	/**
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function setOption($key, $value) {
		$this->options[$key] = $value;
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function hasOption($key) {
		return isset($this->options[$key]);
	}

	/**
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}

}
?>