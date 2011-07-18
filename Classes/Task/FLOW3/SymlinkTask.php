<?php
namespace TYPO3\Deploy\Task\FLOW3;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

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
	 * @return void
	 */
	public function execute($node, $application, $deployment, $options = array()) {
		$releasePath = $deployment->getApplicationReleasePath($application);
		$sharedPath = $application->getOption('deploymentPath') . '/shared';
		$commands = array(
			"mkdir -p $releasePath/Data/Logs",
			"ln -sf $sharedPath/Data/Logs $releasePath/Data/Logs",
			"mkdir -p $releasePath/Data/Persistent",
			"ln -sf $sharedPath/Data/Persistent $releasePath/Data/Persistent"
		);
		$this->shell->execute(implode(';', $commands), $node, $deployment);
	}

}
?>