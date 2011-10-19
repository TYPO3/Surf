<?php
namespace TYPO3\Surf\Task;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use \TYPO3\Surf\Domain\Model\Node;
use \TYPO3\Surf\Domain\Model\Application;
use \TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A task to create initial directories and the release directory for the current release
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CreateDirectoriesTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Executes this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$deploymentPath = $application->getDeploymentPath();
		$sharedPath = $application->getSharedPath();
		$result = $this->shell->execute('test -d ' . $deploymentPath, $node, $deployment, TRUE);
		if ($result === FALSE) {
			throw new \Exception('Deployment directory "' . $deploymentPath . '" does not exist on ' . $node->getName(), 1311003253);
		}
		$this->shell->executeOrSimulate('mkdir -p ' . $deploymentPath . '/releases;mkdir -p ' . $sharedPath, $node, $deployment);
	}

	/**
	 * Simulate this task
	 *
	 * @param Node $node
	 * @param Application $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$this->execute($node, $application, $deployment, $options);
	}

	/**
	 * Rollback this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 * @todo Make the removal of a failed release configurable, sometimes it's necessary to inspect a failed release
	 */
	public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$releasePath = $deployment->getApplicationReleasePath($application);
		$this->shell->execute('rm -rf ' . $releasePath, $node, $deployment, TRUE);
	}

}
?>