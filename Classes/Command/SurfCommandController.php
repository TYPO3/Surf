<?php
namespace TYPO3\Surf\Command;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Surf command controller
 */
class SurfCommandController extends \TYPO3\FLOW3\Cli\CommandController {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Surf\Domain\Service\DeploymentService
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
		$this->response->setExitCode($deployment->getStatus());
	}

	/**
	 * Describe a deployment
	 *
	 * @param string $deploymentName
	 * @return void
	 */
	public function describeCommand($deploymentName) {
		$deployment = $this->deploymentService->getDeployment($deploymentName);

		$this->outputLine('<em> Deployment <b>' . $deployment->getName() . ' </b></em>');
		$this->outputLine();
		$this->outputLine('<u>Workflow</u>: ' . $deployment->getWorkflow()->getName() . PHP_EOL);
		$this->outputLine('<u>Nodes</u>:' . PHP_EOL);
		foreach ($deployment->getNodes() as $node) {
			$this->outputLine('  <b>' . $node->getName() . '</b> (' . $node->getHostname() . ')');
		}
		$this->outputLine(PHP_EOL . '<u>Applications</u>:' . PHP_EOL);
		foreach ($deployment->getApplications() as $application) {
			$this->outputLine('  <b>' . $application->getName() . '</b>' . PHP_EOL);
			$this->outputLine('    <u>Deployment path</u>: ' . $application->getDeploymentPath());
			$this->outputLine('    <u>Options</u>: ');
			foreach ($application->getOptions() as $key => $value) {
				$this->outputLine('      ' . $key . ' => ' . $value);
			}
			$this->outputLine('    <u>Nodes</u>: ' . implode(', ', $application->getNodes()));
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