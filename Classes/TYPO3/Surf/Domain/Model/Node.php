<?php
namespace TYPO3\Surf\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
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
	 * @return \TYPO3\Surf\Domain\Model\Node
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
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
	 * @return \TYPO3\Surf\Domain\Model\Node
	 */
	public function setHostname($hostname) {
		$this->hostname = $hostname;
		return $this;
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
	 * @return \TYPO3\Surf\Domain\Model\Node
	 */
	public function setOptions(array $options) {
		$this->options = $options;
		return $this;
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
	 * @return \TYPO3\Surf\Domain\Model\Node
	 */
	public function setOption($key, $value) {
		$this->options[$key] = $value;
		return $this;
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function hasOption($key) {
		return isset($this->options[$key]);
	}

	/**
	 * @return boolean TRUE if this node is the localhost
	 */
	public function isLocalhost() {
		return $this->hostname === 'localhost';
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