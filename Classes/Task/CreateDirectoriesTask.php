<?php
namespace TYPO3\Deploy\Task;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

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
	 * @return void
	 */
	public function execute($node, $application, $deployment, $options = array()) {
		$path = $application->getOption('deploymentPath');
		$releasePath = $deployment->getApplicationReleasePath($application);
		$result = $this->shell->execute('test -d ' . $path, $node, $deployment);
		if ($result === FALSE) {
			throw new \Exception('Deployment directory ' . $path . ' does not exist on ' . $node->getName(), 1311003253);
		}
		$this->shell->execute('mkdir -p ' . $path . '/releases;mkdir -p ' . $releasePath . ';mkdir -p ' . $path . '/shared', $node, $deployment);
	}

	/**
	 * Rollback this task
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function rollback($node, $application, $deployment) {
		$releasePath = $deployment->getApplicationReleasePath($application);
		$this->shell->execute('rm -rf ' . $releasePath, $node, $deployment);
	}

}
?>