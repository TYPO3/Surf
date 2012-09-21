<?php
namespace TYPO3\Surf\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A Deployment
 *
 * This is the base object exposed to a deployment configuration script and serves as a configuration builder and
 * model for an actual deployment.
 */
class Deployment {

	const STATUS_SUCCESS = 0;
	const STATUS_FAILED = 1;
	const STATUS_CANCELLED = 2;
	const STATUS_UNKNOWN = 3;

	/**
	 * The name of this deployment
	 * @var string
	 */
	protected $name;

	/**
	 * The workflow used for this deployment
	 * @var \TYPO3\Surf\Domain\Model\Workflow
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
	 * Tells if the deployment ran successfully or failed
	 * @var integer
	 */
	protected $status = self::STATUS_UNKNOWN;

	/**
	 * @var boolean
	 */
	protected $initialized = FALSE;

	/**
	 * Constructor
	 *
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * Initialize the deployment
	 *
	 * Must be called before calling deploy() on a deployment.
	 *
	 * A time-based release identifier will be created on initialization. It also executes
	 * callbacks given to the deployment with onInitialize(...).
	 *
	 * @return void
	 * @throws \TYPO3\Surf\Exception
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	public function initialize() {
		if ($this->initialized) {
			throw new \TYPO3\Surf\Exception('Already initialized', 1335976472);
		}
		if ($this->workflow === NULL) {
			throw new InvalidConfigurationException('Workflow must be set before calling initialize()', 1335976479);
		}

		$this->releaseIdentifier = strftime('%Y%m%d%H%M%S', time());
		foreach ($this->applications as $application) {
			$application->registerTasks($this->workflow, $this);
		}
		foreach ($this->initCallbacks as $callback) {
			$callback();
		}

		$this->initialized = TRUE;
	}

	/**
	 * Add a callback to the initialization
	 *
	 * @param callback $callback
	 * @return \TYPO3\Surf\Domain\Model\Deployment
	 */
	public function onInitialize($callback) {
		$this->initCallbacks[] = $callback;
		return $this;
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
	 * It will set dryRun = TRUE which can be inspected by any task.
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
	 * @param \TYPO3\Surf\Domain\Model\Application $application
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
	 * @return \TYPO3\Surf\Domain\Model\Deployment
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get all nodes of this deployment
	 *
	 * @return array The deployment nodes with all application nodes
	 */
	public function getNodes() {
		$nodes = array();
		foreach ($this->applications as $application) {
			foreach ($application->getNodes() as $node) {
				$nodes[$node->getName()] = $node;
			}
		}
		return $nodes;
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
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @return \TYPO3\Surf\Domain\Model\Deployment
	 */
	public function addApplication(Application $application) {
		$this->applications[$application->getName()] = $application;
		return $this;
	}

	/**
	 * Get the Deployment's workflow
	 *
	 * @return \TYPO3\Surf\Domain\Model\Workflow The Deployment's workflow
	 */
	public function getWorkflow() {
		return $this->workflow;
	}

	/**
	 * Sets this Deployment's workflow
	 *
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow The Deployment's workflow
	 * @return \TYPO3\Surf\Domain\Model\Deployment
	 */
	public function setWorkflow($workflow) {
		$this->workflow = $workflow;
		return $this;
	}

	/**
	 *
	 * @param \TYPO3\FLOW3\Log\LoggerInterface $logger
	 * @return \TYPO3\Surf\Domain\Model\Deployment
	 */
	public function setLogger($logger) {
		$this->logger = $logger;
		return $this;
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
	 * @return \TYPO3\Surf\Domain\Model\Deployment
	 */
	public function setDryRun($dryRun) {
		$this->dryRun = $dryRun;
		return $this;
	}

	/**
	 * @param integer $status
	 * @return \TYPO3\Surf\Domain\Model\Deployment
	 */
	public function setStatus($status) {
		$this->status = $status;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->initialized;
	}

}
?>