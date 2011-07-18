<?php
namespace TYPO3\Deploy\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * An application
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
	protected $nodes;

	/**
	 * The options
	 * @var array
	 */
	protected $options = array();

	/**
	 *
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Workflow $workflow
	 */
	public function registerTasks($workflow) {
		$workflow
			->forStage('initialize', array(
				'typo3.deploy:createdirectories'
			))
			->forStage('update', array(
				'typo3.deploy:checkout'
			))
			->forStage('switch', array(
				'typo3.deploy:symlink'
			));
	}

	/**
	 * Get the name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get the Deployment's nodes
	 *
	 * @return array The Deployment's nodes
	 */
	public function getNodes() {
		return $this->nodes;
	}

	/**
	 * Sets this Deployment's nodes
	 *
	 * @param array $nodes The Deployment's nodes
	 * @return void
	 */
	public function setNodes(array $nodes) {
		$this->nodes = $nodes;
	}

	/**
	 * Add a node
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @return void
	 */
	public function addNode(\TYPO3\Deploy\Domain\Model\Node $node) {
		$this->nodes[$node->getName()] = $node;
	}

	/**
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
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
	 * @param array $options 
	 */
	public function setOptions($options) {
		$this->options = $options;
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