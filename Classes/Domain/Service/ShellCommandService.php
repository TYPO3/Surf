<?php
namespace TYPO3\Deploy\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * A shell command service
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ShellCommandService {

	/**
	 * Execute a shell command
	 *
	 * @param string $command
	 * @param \TYPO3\Deploy\Domain\Model\Node $node Node to execute command against, NULL means localhost
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @return TRUE If the command execution was successful (zero return code)
	 */
	public function execute($command, $node, $deployment) {
		if ($node === NULL || $node->getHostname() === 'localhost') {
			return $this->executeLocalCommand($command, $deployment);
		} else {
			return $this->executeRemoteCommand($command, $node, $deployment);
		}
	}

	/**
	 *
	 * @param string $command
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment 
	 * @return boolean
	 */
	public function executeLocalCommand($command, $deployment) {
		$deployment->getLogger()->log('Executing locally: "' . $command . '"', LOG_DEBUG);

		$fp = popen($command, 'r');
		while (($line = fgets($fp)) !== FALSE) {
			$deployment->getLogger()->log('> ' . $line);
	    }
	    $result = fclose($fp);

		return $result === 0;
	}


	/**
	 *
	 * @param string $command
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @return boolean
	 */
	public function executeRemoteCommand($command, $node, $deployment) {
		$deployment->getLogger()->log('Executing on ' . $node->getName() . ': "' . $command . '"', LOG_DEBUG);
		$username = $node->getOption('username');
		$hostname = $node->getHostname();

		$fp = popen('ssh ' . $username . '@' . $hostname . ' ' . escapeshellarg($command) . ' 2>&1', 'r');
		while (($line = fgets($fp)) !== FALSE) {
			$deployment->getLogger()->log('> ' . rtrim($line));
	    }
	    $result = fclose($fp);

		return $result === 0;
	}

}
?>