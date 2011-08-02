<?php
namespace TYPO3\Deploy\Task;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Node;
use \TYPO3\Deploy\Domain\Model\Application;
use \TYPO3\Deploy\Domain\Model\Deployment;

/**
 * A task to create initial directories and the release directory for the current release
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CreateDirectoriesTask extends \TYPO3\Deploy\Domain\Model\Task {

	/**
	 * @inject
	 * @var \TYPO3\Deploy\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Executes this task
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$deploymentPath = $application->getDeploymentPath();
		$sharedPath = $application->getSharedPath();
		$releasePath = $deployment->getApplicationReleasePath($application);
		$result = $this->shell->execute('test -d ' . $deploymentPath, $node, $deployment, TRUE);
		if ($result === FALSE) {
			throw new \Exception('Deployment directory "' . $deploymentPath . '" does not exist on ' . $node->getName(), 1311003253);
		}
		$this->shell->execute('mkdir -p ' . $deploymentPath . '/releases;mkdir -p ' . $sharedPath, $node, $deployment);
	}

	/**
	 * Rollback this task
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
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