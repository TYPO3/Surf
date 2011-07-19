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
	 * The name of this deployment
	 * @var string
	 */
	protected $name;

	/**
	 * The nodes that participate in this deployment
	 * @var array
	 */
	protected $nodes = array();

	/**
	 * The workflow used for this deployment
	 * @var \TYPO3\Deploy\Domain\Model\Workflow
	 */
	protected $workflow;

	/**
	 * The applications deployed with this deployment
	 * @var array
	 */
	protected $applications = array();

	/**
	 * A logger instance used to log messages during deployment
	 * @var \TYPO3\FLOW3\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * The release identifier will be created on each deployment
	 * @var string
	 */
	protected $releaseIdentifier;

	/**
	 * TRUE if the deployment should be simulated
	 * @var string
	 */
	protected $dryRun = FALSE;

	/**
	 * Callbacks that should be executed after initialization
	 * @var array
	 */
	protected $initCallbacks = array();

	/**
	 * Constructor
	 *
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * @return void
	 */
	public function initialize() {
		$this->releaseIdentifier = strftime('%Y%m%d%H%M%S', time());
		foreach ($this->applications as $application) {
			$application->registerTasks($this->workflow);
		}
		foreach ($this->initCallbacks as $callback) {
			$callback();
		}
	}

	/**
	 * Add a callback to the initialization
	 *
	 * @param callback $callback
	 * @return void
	 */
	public function onInitialize($callback) {
		$this->initCallbacks[] = $callback;
	}

	/**
	 * Run this deployment
	 *
	 * @return void
	 */
	public function deploy() {
		$this->logger->log('Deploying ' . $this->name);
		$this->workflow->run($this);
	}

	/**
	 * Simulate this deployment without executing tasks
	 *
	 * @return void
	 */
	public function simulate() {
		$this->setDryRun(TRUE);
		$this->logger->log('Simulating ' . $this->name);
		$this->workflow->run($this);
	}

	/**
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @return string
	 */
	public function getApplicationReleasePath(Application $application) {
		return $application->getDeploymentPath() . '/releases/' . $this->getReleaseIdentifier();
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
	 * @return void
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

	/**
	 * @return boolean
	 */
	public function isDryRun() {
		return $this->dryRun;
	}

	/**
	 * @param boolean $dryRun
	 * @return void
	 */
	public function setDryRun($dryRun) {
		$this->dryRun = $dryRun;
	}

}
?>