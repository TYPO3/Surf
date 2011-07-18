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

		$deployment->addApplication($application);

		$workflow = new \TYPO3\Deploy\Domain\Model\SimpleWorkflow();

		/*
		$workflow->when('update', array('application' => 'FLOW3'), function() {
			
		});
		$workflow->before('update', array('application' => 'FLOW3'), function() {

		});
		$workflow->after('update', array('application' => 'FLOW3'), function() {

		});
		 *
		 */
		$deployment->setWorkflow($workflow);

		$node = new \TYPO3\Deploy\Domain\Model\Node('builder');
		$node->setHostname('myrossmann-integration.dev.networkteam.com');
		// $node->setRoles(array('FLOW3', 'CouchDB'));

		$application->addNode($node);
		$deployment->addNode($node);
		return $deployment;
	}

}
?>