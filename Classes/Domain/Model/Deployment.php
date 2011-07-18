<?php
namespace TYPO3\Deploy\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * A Deployment
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Deployment {

	/**
	 * The name
	 * @var string
	 */
	protected $name;

	/**
	 * The nodes
	 * @var array
	 */
	protected $nodes = array();

	/**
	 * The workflow
	 * @var \TYPO3\Deploy\Domain\Model\Workflow
	 */
	protected $workflow;

	/**
	 * The applications
	 * @var array
	 */
	protected $applications = array();

	/**
	 *
	 * @var \TYPO3\FLOW3\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 *
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * @return void
	 */
	public function init() {
		foreach ($this->applications as $application) {
			$application->registerTasks($this->workflow);
		}
	}

	/**
	 * Run this deployment
	 *
	 * @param \TYPO3\FLOW3\Log\LoggerInterface $logger
	 * @return void
	 */
	public function deploy() {
		$this->logger->log('Deploying ' . $this->name);
		$this->workflow->run($this);
	}

	/**
	 * Get the Deployment's name
	 *
	 * @return string The Deployment's name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets this Deployment's name
	 *
	 * @param string $name The Deployment's name
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
	 * @return array
	 */
	public function getApplications() {
		return $this->applications;
	}

	/**
	 * Add an application
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @return void
	 */
	public function addApplication(\TYPO3\Deploy\Domain\Model\Application $application) {
		$this->applications[$application->getName()] = $application;
	}

	/**
	 * Get the Deployment's workflow
	 *
	 * @return \TYPO3\Deploy\Domain\Model\Workflow The Deployment's workflow
	 */
	public function getWorkflow() {
		return $this->workflow;
	}

	/**
	 * Sets this Deployment's workflow
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Workflow $workflow The Deployment's workflow
	 * @return void
	 */
	public function setWorkflow($workflow) {
		$this->workflow = $workflow;
	}

	/**
	 *
	 * @param \TYPO3\FLOW3\Log\LoggerInterface $logger 
	 */
	public function setLogger($logger) {
		$this->logger = $logger;
	}

	/**
	 *
	 * @return \TYPO3\FLOW3\Log\LoggerInterface
	 */
	public function getLogger() {
		return $this->logger;
	}

}
?>