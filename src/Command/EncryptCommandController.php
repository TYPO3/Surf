<?php
namespace TYPO3\Surf\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Encryption command controller
 */
class EncryptCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Encryption\EncryptionServiceInterface
	 */
	protected $encryptionService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\DeploymentService
	 */
	protected $deploymentService;

	/**
	 * Setup encryption with a local key for the deployment system
	 *
	 * The local key should be kept secretly and could be encrypted with
	 * an optional passphrase. The name defaults to "Local.key".
	 *
	 * @param string $passphrase Passphrase for the generated key (optional)
	 * @param string $configurationPath Path for deployment configuration files
	 * @return void
	 */
	public function setupCommand($passphrase = NULL, $configurationPath = NULL) {
		$deploymentPath = $this->deploymentService->getDeploymentsBasePath($configurationPath);
		if (file_exists($deploymentPath . '/Keys/Local.key')) {
			$this->outputLine('Local key already exists');
			$this->quit(1);
		}
		\TYPO3\Flow\Utility\Files::createDirectoryRecursively($deploymentPath . '/Keys');
		$keyPair = $this->encryptionService->generateKeyPair($passphrase);
		$this->writeKeyPair($keyPair, $deploymentPath . '/Keys/Local.key');
		$this->outputLine('Local key generated');
	}

	/**
	 * Encrypt configuration with the local key
	 *
	 * This command scans the subdirectory of "Build/Deploy/Configuration" for configuration
	 * files that should be encrypted. An optional deployment name restricts this operation to configuration
	 * files of a specific deployment (e.g. "Build/Deploy/Configuration/Staging").
	 *
	 * Only .yaml files with a header of "#!ENCRYPT" are encrypted.
	 *
	 * @param string $deploymentName Optional deployment name to selectively encrypt the configuration
	 * @param string $configurationPath Path for deployment configuration files
	 * @return void
	 * @see typo3.surf:encrypt:open
	 */
	public function sealCommand($deploymentName = '', $configurationPath = NULL) {
		if ($deploymentName !== '') {
			$deployment = $this->deploymentService->getDeployment($deploymentName, $configurationPath);
			$deploymentConfigurationPath = $deployment->getDeploymentConfigurationPath() . '/';
			$deploymentBasePath = $deployment->getDeploymentBasePath();
		} else {
			$deploymentBasePath = $this->deploymentService->getDeploymentsBasePath($configurationPath);
			$deploymentConfigurationPath = $deploymentBasePath;
		}

		$keyPair = $this->readKeyPair($deploymentBasePath . '/Keys/Local.key');
		$configurations = \TYPO3\Flow\Utility\Files::readDirectoryRecursively($deploymentConfigurationPath, 'yaml');
		foreach ($configurations as $configuration) {
			$data = file_get_contents($configuration);
			if (strpos($data, '#!ENCRYPT') !== 0) {
				continue;
			}
			$crypted = $this->encryptionService->encryptData($data, $keyPair->getPublicKey());
			$targetFilename = $configuration . '.encrypted';
			file_put_contents($targetFilename, $crypted);
			unlink($configuration);
			$this->outputLine('Sealed ' . $targetFilename);
		}
	}

	/**
	 * Open encrypted configuration with the local key
	 *
	 * Like the seal command, this can be restricted to a specific deployment. If a passphrase
	 * was used to encrypt the local private key, it must be specified as the passphrase
	 * argument to open the configuration files.
	 *
	 * @param string $passphrase Passphrase to decrypt the local key (if encrypted)
	 * @param string $deploymentName Optional deployment name to selectively decrypt the configuration
	 * @param string $configurationPath Path for deployment configuration files
	 * @return void
	 * @see typo3.surf:encrypt:seal
	 */
	public function openCommand($passphrase = NULL, $deploymentName = '', $configurationPath = NULL) {
		if ($deploymentName !== '') {
			$deployment = $this->deploymentService->getDeployment($deploymentName, $configurationPath);
			$deploymentConfigurationPath = $deployment->getDeploymentConfigurationPath() . '/';
			$deploymentBasePath = $deployment->getDeploymentBasePath();
		} else {
			$deploymentBasePath = $this->deploymentService->getDeploymentsBasePath($configurationPath);
			$deploymentConfigurationPath = $deploymentBasePath;
		}

		$keyPair = $this->readKeyPair($deploymentBasePath . '/Keys/Local.key');
		try {
			$keyPair = $this->encryptionService->openKeyPair($keyPair, $passphrase);
		} catch(\TYPO3\Surf\Encryption\InvalidPassphraseException $exception) {
			$this->outputLine('Local key is encrypted with passphrase. Wrong or no passphrase given.');
			$this->quit(1);
		}
		$configurations = \TYPO3\Flow\Utility\Files::readDirectoryRecursively($deploymentConfigurationPath, 'yaml.encrypted');
		foreach ($configurations as $configuration) {
			$crypted = file_get_contents($configuration);
			$data = $this->encryptionService->decryptData($crypted, $keyPair->getPrivateKey());
			$targetFilename = substr($configuration, 0, -strlen('.encrypted'));
			file_put_contents($targetFilename, $data);
			unlink($configuration);
			$this->outputLine('Opened ' . $targetFilename);
		}
	}

	/**
	 * Writes a key pair to a file
	 *
	 * @param \TYPO3\Surf\Encryption\KeyPair $keyPair
	 * @param string $filename
	 * @return void
	 */
	protected function writeKeyPair(\TYPO3\Surf\Encryption\KeyPair $keyPair, $filename) {
		$data = json_encode(array(
			'encrypted' => $keyPair->isEncrypted(),
			'privateKey' => $keyPair->getPrivateKey(),
			'publicKey' => $keyPair->getPublicKey()
		));
		file_put_contents($filename, $data);
	}

	/**
	 * Reads a key pair from a file
	 *
	 * @param string $filename
	 * @return \TYPO3\Surf\Encryption\KeyPair
	 */
	protected function readKeyPair($filename) {
		$data = file_get_contents($filename);
		$data = json_decode($data, TRUE);
		$keyPair = new \TYPO3\Surf\Encryption\KeyPair($data['privateKey'], $data['publicKey'], $data['encrypted']);
		return $keyPair;
	}

}
?>