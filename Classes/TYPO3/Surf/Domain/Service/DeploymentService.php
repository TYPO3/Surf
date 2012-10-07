<?php
namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A deployment service
 *
 */
class DeploymentService {

	/**
	 * Get available deployment names
	 *
	 * For now it looks up all .php files in the directory FLOW_ROOT/Build/Surf/.
	 *
	 * @return array
	 */
	public function getDeploymentNames() {
		$files = glob(FLOW_PATH_ROOT . 'Build/Surf/*.php');
		return array_map(function($file) {
			return substr($file, strlen(FLOW_PATH_ROOT . 'Build/Surf/'), -4);
		}, $files);
	}

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
		$deploymentPathAndFilename = FLOW_PATH_ROOT . 'Build/Surf/' . $deploymentName . '.php';
		if (!file_exists($deploymentPathAndFilename)) {
			exit(sprintf ("The deployment file %s does not exist.\n", $deploymentPathAndFilename));
		}

		$deployment = new Deployment($deploymentName);
		require($deploymentPathAndFilename);
		return $deployment;
	}

}
?>