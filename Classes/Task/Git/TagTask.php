<?php
namespace TYPO3\Surf\Task\Git;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use \TYPO3\Surf\Domain\Model\Node;
use \TYPO3\Surf\Domain\Model\Application;
use \TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A task which can be used to tag a git repository and its submodules
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TagTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$targetPath = $deployment->getApplicationReleasePath($application);

		if (!isset($options['tagName'])) {
			throw new \Exception('tagName not set', 1314186541);
		}

		if (!isset($options['description'])) {
			throw new \Exception('description not set', 1314186553);
		}

		$targetPath = $deployment->getApplicationReleasePath($application);
		$this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git tag -f -a -m "%s" %s', $options['description'], $options['tagName']), $node, $deployment);
		$this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git submodule foreach \'git tag -f -a -m "%s" %s\'', $options['description'], $options['tagName']), $node, $deployment);
	}

	/**
	 * Simulate this task
	 *
	 * @param Node $node
	 * @param Application $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$this->execute($node, $application, $deployment, $options);
	}

}
?>