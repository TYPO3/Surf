<?php
namespace TYPO3\Deploy\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Workflow;
use \TYPO3\Deploy\Domain\Model\Application;
use \TYPO3\Deploy\Domain\Model\Node;

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
	 * @var string
	 */
	protected $releaseIdentifier;

	/**
	 * @var array
	 */
	protected $initCallbacks = array();

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
		$this->releaseIdentifier = strftime('%Y%m%d%H%M%S', time());
		foreach ($this->applications as $application) {
			$application->registerTasks($this->workflow);
		}
		foreach ($this->initCallbacks as $callback) {
			$callback();
		}
	}

	/**
	 *
	 * @param callback $callback
	 */
	public function override($callback) {
		$this->initCallbacks[] = $callback;
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
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @return string
	 */
	public function getApplicationReleasePath(Application $application) {
		return $application->getOption('deploymentPath') . '/releases/' . $this->getReleaseIdentifier();
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
	public function addNode(Node $node) {
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
	public function addApplication(Application $application) {
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

	/**
	 *
	 * @return string
	 */
	public function getReleaseIdentifier() {
		return $this->releaseIdentifier;
	}

}
?>