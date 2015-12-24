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
	 * Will look up all .php files in the directory FLOW_ROOT/Build/Surf/ or the given path if specified.
	 *
	 * @param string $path
	 * @return array
	 */
	public function getDeploymentNames($path = NULL) {
		$path = $this->getDeploymentsBasePath($path);
		$files = glob($path . '/*.php');
		return array_map(function($file) use ($path) {
			return substr($file, strlen($path) + 1, -4);
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
	 * @param string $path
	 * @return \TYPO3\Surf\Domain\Model\Deployment
	 */
	public function getDeployment($deploymentName, $path = NULL) {
		$deploymentConfigurationPath = $this->getDeploymentsBasePath($path);
		$deploymentPathAndFilename = $deploymentConfigurationPath . '/' . $deploymentName . '.php';
		if (!file_exists($deploymentPathAndFilename)) {
			exit(sprintf ("The deployment file %s does not exist.\n", $deploymentPathAndFilename));
		}

		$deployment = new Deployment($deploymentName);
		$deployment->setDeploymentBasePath($deploymentConfigurationPath);
		require($deploymentPathAndFilename);
		return $deployment;
	}

	/**
	 * Get the root path of the surf deployment declarations
	 *
	 * This defaults to FLOW_PATH_ROOT/Build/Surf if a NULL path is given.
	 *
	 * @param string $path An absolute or FLOW_PATH_ROOT relative path (optional)
	 * @return string The configuration root path without a trailing slash.
	 */
	public function getDeploymentsBasePath($path = NULL) {
		$path = ($path ?: 'Build/Surf/');
		if (substr($path, 0, 1) !== '/') {
			$path = FLOW_PATH_ROOT . $path;
		}
		if (substr($path, -1) === '/') {
			$path = substr($path, 0, -1);
		}
		return $path;
	}

}
?>