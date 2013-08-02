<?php
namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A shell command service
 *
 */
class ShellCommandService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 */
	protected $packageManager;

	/**
	 * Execute a shell command (locally or remote depending on the node hostname)
	 *
	 * @param mixed $command The shell command to execute, either string or array of commands
	 * @param \TYPO3\Surf\Domain\Model\Node $node Node to execute command against, NULL means localhost
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param boolean $ignoreErrors If this command should ignore exit codes unequeal zero
	 * @param boolean $logOutput TRUE if the output of the command should be logged
	 * @return mixed The output of the shell command or FALSE if the command returned a non-zero exit code and $ignoreErrors was enabled.
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
	 */
	public function execute($command, Node $node, Deployment $deployment, $ignoreErrors = FALSE, $logOutput = TRUE) {
		if ($node === NULL || $node->isLocalhost()) {
			list($exitCode, $returnedOutput) = $this->executeLocalCommand($command, $deployment, $logOutput);
		} else {
			list($exitCode, $returnedOutput) = $this->executeRemoteCommand($command, $node, $deployment, $logOutput);
		}
		if ($ignoreErrors !== TRUE && $exitCode !== 0) {
			$deployment->getLogger()->log(rtrim($returnedOutput), LOG_WARNING);
			throw new \TYPO3\Surf\Exception\TaskExecutionException('Command returned non-zero return code: ' . $exitCode, 1311007746);
		}
		return ($exitCode === 0 ? $returnedOutput : FALSE);
	}

	/**
	 * Simulate a command by just outputting what would be executed
	 *
	 * @param string $command
	 * @param Node $node
	 * @param Deployment $deployment
	 * @return bool
	 */
	public function simulate($command, Node $node, Deployment $deployment) {
		if ($node === NULL || $node->isLocalhost()) {
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
	 * @param boolean $logOutput TRUE if the output of the command should be logged
	 * @return boolean|mixed
	 */
	public function executeOrSimulate($command, Node $node, Deployment $deployment, $ignoreErrors = FALSE, $logOutput = TRUE) {
		if (!$deployment->isDryRun()) {
			return $this->execute($command, $node, $deployment, $ignoreErrors, $logOutput);
		} else {
			return $this->simulate($command, $node, $deployment);
		}
	}

	/**
	 * Execute a shell command locally
	 *
	 * @param mixed $command
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param boolean $logOutput TRUE if the output of the command should be logged
	 * @return array
	 */
	protected function executeLocalCommand($command, Deployment $deployment, $logOutput = TRUE) {
		$command = $this->prepareCommand($command);
		$deployment->getLogger()->log('(localhost): "' . $command . '"', LOG_DEBUG);

		return $this->executeProcess($deployment, $command, $logOutput, '> ');
	}


	/**
	 * Execute a shell command via SSH
	 *
	 * @param mixed $command
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param boolean $logOutput TRUE if the output of the command should be logged
	 * @return array
	 */
	protected function executeRemoteCommand($command, Node $node, Deployment $deployment, $logOutput = TRUE) {
		$command = $this->prepareCommand($command);
		$deployment->getLogger()->log('$' . $node->getName() . ': "' . $command . '"', LOG_DEBUG);

		if ($node->hasOption('remoteCommandExecutionHandler')) {
			$remoteCommandExecutionHandler = $node->getOption('remoteCommandExecutionHandler');
			/** @var $remoteCommandExecutionHandler callable */
			return $remoteCommandExecutionHandler($this, $command, $node, $deployment, $logOutput);
		}

		$username = $node->hasOption('username') ? $node->getOption('username') : NULL;
		if (!empty($username)) {
			$username = $username . '@';
		}
		$hostname = $node->getHostname();

			// TODO Get SSH options from node or deployment
		$sshOptions = array('-A');
		if ($node->hasOption('port')) {
			$sshOptions[] = '-p ' . escapeshellarg($node->getOption('port'));
		}
		if ($node->hasOption('password')) {
			$sshOptions[] = '-o PubkeyAuthentication=no';
		}

		$sshCommand = 'ssh ' . implode(' ', $sshOptions) . ' ' . escapeshellarg($username . $hostname) . ' ' . escapeshellarg($command) . ' 2>&1';

		if ($node->hasOption('password')) {
			$surfPackage = $this->packageManager->getPackage('TYPO3.Surf');
			$passwordSshLoginScriptPathAndFilename = \TYPO3\Flow\Utility\Files::concatenatePaths(array($surfPackage->getResourcesPath(), 'Private/Scripts/PasswordSshLogin.expect'));
			$sshCommand = sprintf('expect %s %s %s', escapeshellarg($passwordSshLoginScriptPathAndFilename), escapeshellarg($node->getOption('password')), $sshCommand);
		}

		return $this->executeProcess($deployment, $sshCommand, $logOutput, '    > ');
	}

	/**
	 * Open a process with popen and process each line by logging and
	 * collecting its output.
	 *
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param string $command
	 * @param boolean $logOutput
	 * @param string $logPrefix
	 * @return array The exit code of the command and the returned output
	 */
	public function executeProcess($deployment, $command, $logOutput, $logPrefix) {
		$returnedOutput = '';
		$fp = popen($command, 'r');
		while (($line = fgets($fp)) !== FALSE) {
			if ($logOutput) {
				$deployment->getLogger()->log($logPrefix . rtrim($line), LOG_DEBUG);
			}
			$returnedOutput .= $line;
		}
		$exitCode = pclose($fp);
		return array($exitCode, trim($returnedOutput));
	}

	/**
	 * Prepare a command
	 *
	 * @param mixed $command
	 * @return string
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
	 */
	protected function prepareCommand($command) {
		if (is_string($command)) {
			return trim($command);
		} elseif (is_array($command)) {
			return implode(' && ', $command);
		} else {
			throw new \TYPO3\Surf\Exception\TaskExecutionException('Command must be string or array, ' . gettype($command) . ' given.', 1312454906);
		}
	}

}
?>
