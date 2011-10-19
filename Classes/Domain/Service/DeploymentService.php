<?php
namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A deployment service
 *
 */
class DeploymentService {

	/**
	 * Get a deployment object by deployment name
	 *
	 * Looks up the deployment in directory FLOW_ROOT/Build/Surf/[deploymentName].php
	 *
	 * The script has access to a deployment object as "$deployment". This could change
	 * in the future.
	 *
	 * @param string $deploymentName
	 * @return \TYPO3\Surf\Domain\Model\Deployment
	 */
	public function getDeployment($deploymentName) {
		$deploymentPathAndFilename = FLOW3_PATH_ROOT . 'Build/Surf/' . $deploymentName . '.php';
		if (!file_exists($deploymentPathAndFilename)) {
			exit(sprintf ("The deployment file %s does not exist.\n", $deploymentPathAndFilename));
		}

		$deployment = new Deployment($deploymentName);
		require($deploymentPathAndFilename);
		return $deployment;
	}

}
?>