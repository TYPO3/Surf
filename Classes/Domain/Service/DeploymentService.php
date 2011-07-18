<?php
namespace TYPO3\Deploy\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

/**
 * A deployment service
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DeploymentService {

	/**
	 *
	 * @param string $deploymentName
	 * @return \TYPO3\Deploy\Domain\Model\Deployment
	 */
	public function getDeployment($deploymentName) {
		$deployment = new \TYPO3\Deploy\Domain\Model\Deployment('Integration');

		$application = new \TYPO3\Deploy\Applications\FLOW3();
		$application->setOption('repositoryUrl', 'ssh://review.networkteam.com:29418/flow3/projects/rossmann/distributions/MyRossmann.git');
		$application->setOption('deploymentPath', '/home/flow3-integration/sites/myrossmann-deploy');
		$deployment->addApplication($application);

		$workflow = new \TYPO3\Deploy\Domain\Model\SimpleWorkflow();
		$deployment->setWorkflow($workflow);

		$node = new \TYPO3\Deploy\Domain\Model\Node('builder');
		$node->setHostname('myrossmann-integration.dev.networkteam.com');
		$node->setOption('username', 'flow3-integration');

		// TODO Make application options overridable per node

		$application->addNode($node);

		$deployment->addNode($node);
		return $deployment;
	}

}
?>