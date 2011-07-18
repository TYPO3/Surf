<?php
namespace TYPO3\Deploy\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * A Node
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Node {

	/**
	 * The name
	 * @var string
	 */
	protected $name;

	/**
	 * The roles
	 * @var array
	 */
	protected $roles;

	/**
	 * The hostname
	 * @var string
	 */
	protected $hostname;

	/**
	 * The options
	 * @var array
	 */
	protected $options;

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
	 * Get the Node's roles
	 *
	 * @return array The Node's roles
	 */
	public function getRoles() {
		return $this->roles;
	}

	/**
	 * Sets this Node's roles
	 *
	 * @param array $roles The Node's roles
	 * @return void
	 */
	public function setRoles(array $roles) {
		$this->roles = $roles;
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
	 */
	public function setOption($key, $value) {
		$this->options[$key] = $value;
	}

}
?>