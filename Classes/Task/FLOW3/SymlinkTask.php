<?php
namespace TYPO3\Deploy\Task\FLOW3;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Node;
use \TYPO3\Deploy\Domain\Model\Application;
use \TYPO3\Deploy\Domain\Model\Deployment;

/**
 * A symlink task for linking shared directories
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
		$commands = array(
			"mkdir -p $releasesPath/$releaseIdentifier/Data",
			"cd $releasesPath/$releaseIdentifier",
			"ln -sf ../../../shared/Data/Logs ./Data/Logs",
			"ln -sf ../../../shared/Data/Persistent ./Data/Persistent",
			"ln -sf ../../../shared/Configuration/Production ./Configuration/Production"
		);
		$this->shell->execute($commands, $node, $deployment);
	}

}
?>