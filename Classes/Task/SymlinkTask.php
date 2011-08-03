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
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$releaseIdentifier = $deployment->getReleaseIdentifier();
		$releasesPath = $application->getDeploymentPath() . '/releases';
		$this->shell->execute('cd ' . $releasesPath . ' && rm -f ./previous && if [ -e ./current ]; then mv ./current ./previous; fi && ln -s ./' . $releaseIdentifier . ' ./current', $node, $deployment);
		$deployment->getLogger()->log('Node "' . $node->getName() . '" is live!');
	}

	/**
	 * Rollback this task
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$releasesPath = $application->getDeploymentPath() . '/releases';
		$this->shell->execute('cd ' . $releasesPath . ' && rm -f ./current && mv ./previous ./current', $node, $deployment, TRUE);
	}

}
?>
