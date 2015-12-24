<?php
namespace TYPO3\Surf\Application\TYPO3;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

/**
 * A TYPO3 CMS application template
 * @TYPO3\Flow\Annotations\Proxy(false)
 */
class CMS extends \TYPO3\Surf\Application\BaseApplication {

	/**
	 * Constructor
	 *
	 * @param string $name
	 */
	public function __construct($name = 'TYPO3 CMS') {
		parent::__construct($name);
	}

}
?>