<?php
declare(ENCODING = 'utf-8');
namespace TYPO3\Deploy\Command;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * Deploy command controller
 */
class DeployCommandController extends \TYPO3\FLOW3\MVC\Controller\CommandController {

	/**
	 * @inject
	 * @var \TYPO3\Deploy\Domain\Service\DeploymentService
	 */
	protected $deploymentService;

	/**
	 * Run a deployment
	 *
	 * @param string $deploymentName
	 * @return void
	 */
	public function deployCommand($deploymentName) {
		$logger = new \TYPO3\FLOW3\Log\Logger();
		$console = new \TYPO3\FLOW3\Log\Backend\ConsoleBackend(array('severityThreshold' => LOG_DEBUG));
		$console->open();
		$logger->setBackend($console);

		$deployment = $this->deploymentService->getDeployment($deploymentName);
		$deployment->setLogger($logger);
		$deployment->initialize();

		$deployment->deploy();
	}

	/**
	 * Describe a deployment
	 *
	 * @param string $deploymentName
	 * @return void
	 */
	public function describeCommand($deploymentName) {
		$deployment = $this->deploymentService->getDeployment($deploymentName);

		echo 'Deployment "' . $deployment->getName() . '"' . PHP_EOL;
		echo str_repeat('_', 80) . PHP_EOL;
		echo 'Workflow: ' . $deployment->getWorkflow()->getName() . PHP_EOL;
		echo 'Nodes: '  . PHP_EOL;
		foreach ($deployment->getNodes() as $node) {
			echo '  - ' . $node->getName() . ' (' . $node->getHostname() . ')' . PHP_EOL;
		}
		echo 'Applications: '  . PHP_EOL;
		foreach ($deployment->getApplications() as $application) {
			echo '  - ' . $application->getName() . PHP_EOL;
			echo '    Deployment path: ' . $application->getDeploymentPath() . PHP_EOL;
			echo '    Options: ' . PHP_EOL;
			foreach ($application->getOptions() as $key => $value) {
				echo '      ' . $key . ' => ' . $value . PHP_EOL;
			}
			echo '    Nodes:' . PHP_EOL;
			foreach ($application->getNodes() as $node) {
				echo '      - ' . $node->getName() . PHP_EOL;
			}
		}
	}

	/**
	 * Simulate a deployment
	 *
	 * @param string $deploymentName
	 * @return void
	 */
	public function simulateCommand($deploymentName) {
		$logger = new \TYPO3\FLOW3\Log\Logger();
		$console = new \TYPO3\FLOW3\Log\Backend\ConsoleBackend(array('severityThreshold' => LOG_DEBUG));
		$console->open();
		$logger->setBackend($console);

		$deployment = $this->deploymentService->getDeployment($deploymentName);
		$deployment->setLogger($logger);
		$deployment->initialize();

		$deployment->simulate();
	}

}
?>