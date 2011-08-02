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
 * A cleanup task to delete old (unused) releases
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CleanupReleasesTask extends \TYPO3\Deploy\Domain\Model\Task {

	/**
	 * @inject
	 * @var \TYPO3\Deploy\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Cleanup old releases by listing all releases and keeping a configurable
	 * number of old releases (application option "keepReleases"). The current
	 * and previous release (if one exists) are protected from removal.
	 *
	 * Note: There is no rollback for this cleanup, so we have to be sure not to delete any
	 *       live or referenced releases.
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		if (!$application->hasOption('keepReleases')) {
			$deployment->getLogger()->log('Keeping all releases for "' . $application->getName() . '"', LOG_DEBUG);
			return;
		}

		$keepReleases = $application->getOption('keepReleases');
		$releasesPath = $application->getDeploymentPath() . '/releases';
		$currentReleaseIdentifier = $deployment->getReleaseIdentifier();
		$previousReleasePath = $application->getDeploymentPath() . '/previous';
		$previousReleaseIdentifier = trim($this->shell->execute("if [ -h $previousReleasePath ]; then basename `readlink $previousReleasePath` ; fi", $node, $deployment));

		$allReleasesList = $this->shell->execute("find $releasesPath/. -maxdepth 1 -type d -exec basename {} \;", $node, $deployment);
		$allReleases = preg_split('/\s+/', $allReleasesList, -1, PREG_SPLIT_NO_EMPTY);

		$removableReleases = array();
		foreach ($allReleases as $release) {
			if ($release !== '.' && $release !== $currentReleaseIdentifier && $release !== $previousReleaseIdentifier) {
				$removableReleases[] = trim($release);
			}
		}
		sort($removableReleases);

		$removeReleases = array_slice($removableReleases, 0, count($removableReleases) - $keepReleases);
		$removeCommand = '';
		foreach ($removeReleases as $removeRelease) {
			$removeCommand .= "rm -rf {$releasesPath}/{$removeRelease};rm -rf {$releasesPath}/{$removeRelease}REVISION;";
		}
		if (count($removeReleases) > 0) {
			$deployment->getLogger()->log('Removing releases ' . implode(', ', $removeReleases));
			$this->shell->execute($removeCommand, $node, $deployment);
		} else {
			$deployment->getLogger()->log('No releases to remove', LOG_DEBUG);
		}
	}

}
?>