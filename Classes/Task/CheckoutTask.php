<?php
namespace TYPO3\Deploy\Task;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * A generic checkout task
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CheckoutTask extends \TYPO3\Deploy\Domain\Model\Task {

	/**
	 * @inject
	 * @var \TYPO3\Deploy\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * @param $node
	 * @param $application
	 * @param $deployment
	 * @return void
	 */
	public function execute($node, $application, $deployment, $options = array()) {
		$targetPath = $deployment->getApplicationReleasePath($application);
		$this->shell->execute('git clone --recursive ' . $application->getOption('repositoryUrl') . ' ' . $targetPath, $node, $deployment);
	}

}
?>