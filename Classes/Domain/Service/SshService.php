<?php
namespace TYPO3\Deploy\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * A SSH service
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SshService {

	/**
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param string $command
	 */
	public function executeCommand($node, $command) {
		exec('ssh -c ', $output);
	}

}
?>