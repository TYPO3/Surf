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
		$console = new \TYPO3\FLOW3\Log\Backend\ConsoleBackend();
		$console->open();
		$logger->setBackend($console);


		$deployment = $this->deploymentService->getDeployment($deploymentName);
		$deployment->setLogger($logger);
		$deployment->init();

		$deployment->deploy();
	}

}
?>