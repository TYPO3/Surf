<?php
namespace TYPO3\Surf\Encryption;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Key pair
 *
 * A key pair stores a public / private key pair with an open or encrypted
 * private key.
 *
 * @FLOW3\Scope("prototype")
 * @FLOW3\ValueObject
 */
class KeyPair {

	/**
	 * @var string
	 */
	protected $privateKey;

	/**
	 * @var string
	 */
	protected $publicKey;

	/**
	 * @var boolean
	 */
	protected $encrypted;

	/**
	 * Constructor
	 *
	 * @param string $privateKey
	 * @param string $publicKey
	 * @param boolean $encrypted
	 */
	public function __construct($privateKey, $publicKey, $encrypted = FALSE) {
		$this->privateKey = $privateKey;
		$this->publicKey = $publicKey;
		$this->encrypted = $encrypted;
	}

	/**
	 * @return string
	 */
	public function getPrivateKey() {
		return $this->privateKey;
	}

	/**
	 * @return string
	 */
	public function getPublicKey() {
		return $this->publicKey;
	}

	/**
	 * @return boolean
	 */
	public function isEncrypted() {
		return $this->encrypted;
	}

}
?>