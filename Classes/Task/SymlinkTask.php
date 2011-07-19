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
 * A symlink task for switching over the current directory to the new release
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SymlinkTask extends \TYPO3\Deploy\Domain\Model\Task {

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
	public function execute(Node $node, Application $application, Deployment $deployment, $options = array()) {
		$releasePath = $deployment->getApplicationReleasePath($application);
		$currentPath = $application->getDeploymentPath() . '/current';
		$previousPath = $application->getDeploymentPath() . '/previous';
		$this->shell->execute('rm -f ' . $previousPath . ' && if [ -e ' . $currentPath . ' ]; then mv ' . $currentPath . ' ' . $previousPath . '; fi && ln -s ' . $releasePath . ' ' . $currentPath, $node, $deployment, TRUE);
		$deployment->getLogger()->log('You are live!');
	}

	/**
	 * Rollback this task
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function rollback(Node $node, Application $application, Deployment $deployment) {
		$releasePath = $deployment->getApplicationReleasePath($application);
		$currentPath = $application->getDeploymentPath() . '/current';
		$previousPath = $application->getDeploymentPath() . '/previous';
		$this->shell->execute('rm -f ' . $currentPath . ' && mv ' . $previousPath . ' ' . $currentPath, $node, $deployment);
	}

}
?>