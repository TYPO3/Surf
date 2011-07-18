<?php
namespace TYPO3\Deploy\Task\FLOW3;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * A task to create FLOW3 specific directories
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
	 * Execute this task
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function execute($node, $application, $deployment, $options = array()) {
		$path = $application->getOption('deploymentPath');
		$this->shell->execute('mkdir -p ' . $path . '/shared/Data/Logs;mkdir -p ' . $path . '/shared/Data/Persistent;mkdir -p ' . $path . '/shared/Configuration;mkdir -p ' . $path . '/shared/Web/_Resources', $node, $deployment);
	}

}
?>