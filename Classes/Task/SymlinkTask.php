<?php
namespace TYPO3\Deploy\Task;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * A symlink task for switching over
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SymlinkTask extends \TYPO3\Deploy\Domain\Model\Task {

	/**
	 * Execute this task
	 *
	 * @param $node
	 * @param $application
	 * @param $deployment
	 * @return void
	 */
	public function execute($node, $application, $deployment, $options = array()) {
		$deployment->getLogger()->log('Symlink');
	}

}
?>