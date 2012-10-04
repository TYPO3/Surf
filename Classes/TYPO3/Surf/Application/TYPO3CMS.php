<?php
namespace TYPO3\Surf\Application;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

/**
 * A TYPO3CMS application template
 * @TYPO3\Flow\Annotations\Proxy(false)
 */
class TYPO3CMS extends BaseApplication {

	/**
	 * Constructor
	 *
	 * @param string $name
	 */
	public function __construct($name = 'TYPO3CMS') {
		parent::__construct($name);
	}

}
?>