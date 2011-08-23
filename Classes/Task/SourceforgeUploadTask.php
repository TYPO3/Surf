<?php
namespace TYPO3\Deploy\Task;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Node;
use \TYPO3\Deploy\Domain\Model\Application;
use \TYPO3\Deploy\Domain\Model\Deployment;

/**
 * Task for uploading to sourceforge
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SourceforgeUploadTask extends \TYPO3\Deploy\Domain\Model\Task {

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
		$this->checkOptionsForValidity($options);
		$projectName = $options['sourceforgeProjectName'];

		$sourceforgeLogin = $options['sourceforgeUserName'] . ',' . $options['sourceforgeProjectName'];

		$projectDirectory = sprintf('/home/frs/project/%s/%s/%s/%s/%s', substr($projectName, 0, 1), substr($projectName, 0, 2), $projectName, $options['sourceforgePackageName'], $options['version']);
		$this->shell->execute('rsync -e ssh ' . implode(' ', $options['files']) . ' ' . $sourceforgeLogin . '@frs.sourceforge.net:' . $projectDirectory, $node, $deployment);
	}

	/**
	 * Check if all required options are given
	 *
	 * @param array $options
	 */
	protected function checkOptionsForValidity($options) {
		if (!isset($options['sourceforgeProjectName'])) {
			throw new \Exception('Sourceforge Project Name not set', 1314170122);
		}

		if (!isset($options['sourceforgePackageName'])) {
			throw new \Exception('Sourceforge Package Name not set', 1314170132);
		}

		if (!isset($options['sourceforgeUserName'])) {
			throw new \Exception('Sourceforge User Name not set', 1314170145);
		}

		if (!isset($options['version'])) {
			throw new \Exception('version not set', 1314170151);
		}

		if (!isset($options['files'])) {
			throw new \Exception('files to upload not set', 1314170162);
		}

		if (!is_array($options['files'])) {
			throw new \Exception('files to upload is no array', 1314170175);
		}
	}
}
?>