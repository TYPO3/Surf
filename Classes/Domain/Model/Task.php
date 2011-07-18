<?php
namespace TYPO3\Deploy\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * A task
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class Task {

	/**
	 * Execute this action
	 *
	 * @param $node
	 * @param $application
	 * @param $deployment
	 * @param $options
	 * @return void
	 */
	abstract public function execute($node, $application, $deployment, $options = array());

}
?>