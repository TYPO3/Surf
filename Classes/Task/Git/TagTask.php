<?php
namespace TYPO3\Deploy\Task\Git;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Node;
use \TYPO3\Deploy\Domain\Model\Application;
use \TYPO3\Deploy\Domain\Model\Deployment;

/**
 * A task which can be used to tag a git repository and its submodules
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TagTask extends \TYPO3\Deploy\Domain\Model\Task {

	/**
	 * @inject
	 * @var \TYPO3\Deploy\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
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
		$this->shell->execute(sprintf('cd ' . $targetPath . '; git tag -f -a -m "%s" %s', $options['description'], $options['tagName']), $node, $deployment);
		$this->shell->execute(sprintf('cd ' . $targetPath . '; git submodule foreach \'git tag -f -a -m "%s" %s\'', $options['description'], $options['tagName']), $node, $deployment);
	}
}
?>