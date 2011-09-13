<?php
namespace TYPO3\Deploy\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Deployment;

/**
 * A deployment service
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DeploymentService {

	/**
	 * Get a deployment object by deployment name
	 *
	 * Looks up the deployment in directory FLOW_ROOT/Build/Deploy/[deploymentName].php
	 *
	 * The script has access to a deployment object as "$deployment". This could change
	 * in the future.
	 *
	 * @param string $deploymentName
	 * @return \TYPO3\Deploy\Domain\Model\Deployment
	 */
	public function getDeployment($deploymentName) {
		$deploymentPathAndFilename = FLOW3_PATH_ROOT . 'Build/Deploy/' . $deploymentName . '.php';
		if (!file_exists($deploymentPathAndFilename)) {
			exit(sprintf ("The deployment file %s does not exist.\n", $deploymentPathAndFilename));
		}

		$deployment = new Deployment($deploymentName);
		require($deploymentPathAndFilename);
		return $deployment;
	}

}
?>