<?php
namespace TYPO3\Deploy\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Node;
use \TYPO3\Deploy\Domain\Model\Deployment;

/**
 * A shell command service
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ShellCommandService {

	/**
	 * Execute a shell command (locally or remote depending on the node hostname)
	 *
	 * @param mixed $command The shell command to execute, either string or array of commands
	 * @param \TYPO3\Deploy\Domain\Model\Node $node Node to execute command against, NULL means localhost
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @param boolean $ignoreErrors If this command should ignore exit codes unequeal zero
	 * @return mixed The output of the shell command or FALSE if the command returned a non-zero exit code and $ignoreErrors was enabled.
	 */
	public function execute($command, Node $node, Deployment $deployment, $ignoreErrors = FALSE) {
		if ($node === NULL || $node->getHostname() === 'localhost') {
			list($exitCode, $returnedOutput) = $this->executeLocalCommand($command, $deployment);
		} else {
			list($exitCode, $returnedOutput) = $this->executeRemoteCommand($command, $node, $deployment);
		}
		if ($ignoreErrors !== TRUE && $exitCode !== 0) {
			throw new \Exception('Command returned non-zero return code', 1311007746);
		}
		return ($exitCode === 0 ? $returnedOutput : FALSE);
	}

	/**
	 * Simulate a command by just outputting what would be executed
	 *
	 * @param string $command
	 * @param Node $node
	 * @param Deployment $deployment
	 * @param boolean $ignoreErrors
	 */
	public function simulate($command, Node $node, Deployment $deployment, $ignoreErrors = FALSE) {
		if ($node === NULL || $node->getHostname() === 'localhost') {
			$command = $this->prepareCommand($command);
			$deployment->getLogger()->log('... (localhost): "' . $command . '"', LOG_DEBUG);
		} else {
			$command = $this->prepareCommand($command);
			$deployment->getLogger()->log('... $' . $node->getName() . ': "' . $command . '"', LOG_DEBUG);
		}
		return TRUE;
	}

	/**
	 * Execute or simulate a command (if the deployment is in dry run mode)
	 *
	 * @param string $command
	 * @param Node $node
	 * @param Deployment $deployment
	 * @param boolean $ignoreErrors
	 */
	public function executeOrSimulate($command, Node $node, Deployment $deployment, $ignoreErrors = FALSE) {
		if (!$deployment->isDryRun()) {
			return $this->execute($command, $node, $deployment, $ignoreErrors);
		} else {
			return $this->simulate($command, $node, $deployment, $ignoreErrors);
		}
	}

	/**
	 * Execute a shell command locally
	 *
	 * @param mixed $command
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @return array
	 */
	protected function executeLocalCommand($command, Deployment $deployment) {
		$command = $this->prepareCommand($command);
		$deployment->getLogger()->log('    (localhost): "' . $command . '"', LOG_DEBUG);
		$returnedOutput = '';

		$fp = popen($command, 'r');
		while (($line = fgets($fp)) !== FALSE) {
			$deployment->getLogger()->log('> ' . $line);
			$returnedOutput .= $line;
		}
		$exitCode = pclose($fp);

		return array($exitCode, $returnedOutput);
	}


	/**
	 * Execute a shell command via SSH
	 *
	 * @param mixed $command
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @return array
	 */
	protected function executeRemoteCommand($command, Node $node, Deployment $deployment) {
		$command = $this->prepareCommand($command);
		$deployment->getLogger()->log('    $' . $node->getName() . ': "' . $command . '"', LOG_DEBUG);
		$username = $node->getOption('username');
		$hostname = $node->getHostname();
		$returnedOutput = '';

		// TODO Get SSH options from node or deployment
		$fp = popen('ssh -A ' . $username . '@' . $hostname . ' ' . escapeshellarg($command) . ' 2>&1', 'r');
		while (($line = fgets($fp)) !== FALSE) {
			$deployment->getLogger()->log('    > ' . rtrim($line));
			$returnedOutput .= $line;
		}
		$exitCode = pclose($fp);

		return array($exitCode, $returnedOutput);
	}

	/**
	 * Prepare a command
	 *
	 * @param mixed $command
	 * @return string
	 */
	protected function prepareCommand($command) {
		if (is_string($command)) {
			return trim($command);
		} elseif (is_array($command)) {
			return implode(';', $command);
		} else {
			throw new \Exception('Command must be string or array', 1312454906);
		}
	}

}
?>