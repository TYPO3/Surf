<?php
namespace TYPO3\Surf\Task;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * A cleanup task to delete old (unused) releases
 *
 */
class CleanupReleasesTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Cleanup old releases by listing all releases and keeping a configurable
	 * number of old releases (application option "keepReleases"). The current
	 * and previous release (if one exists) are protected from removal.
	 *
	 * Example configuration:
	 *
	 *     $application->setOption('keepReleases', 2);
	 *
	 * Note: There is no rollback for this cleanup, so we have to be sure not to delete any
	 *       live or referenced releases.
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		if (!isset($options['keepReleases'])) {
			$deployment->getLogger()->log(($deployment->isDryRun() ? 'Would keep' : 'Keeping') . ' all releases for "' . $application->getName() . '"', LOG_DEBUG);
			return;
		}

		$keepReleases = $options['keepReleases'];
		$releasesPath = $application->getDeploymentPath() . '/releases';
		$currentReleaseIdentifier = $deployment->getReleaseIdentifier();
		$previousReleasePath = $application->getDeploymentPath() . '/releases/previous';
		$previousReleaseIdentifier = trim($this->shell->execute("if [ -h $previousReleasePath ]; then basename `readlink $previousReleasePath` ; fi", $node, $deployment));

		$allReleasesList = $this->shell->execute("if [ -d $releasesPath/. ]; then find $releasesPath/. -maxdepth 1 -type d -exec basename {} \; ; fi", $node, $deployment);
		$allReleases = preg_split('/\s+/', $allReleasesList, -1, PREG_SPLIT_NO_EMPTY);

		$removableReleases = array();
		foreach ($allReleases as $release) {
			if ($release !== '.' && $release !== $currentReleaseIdentifier && $release !== $previousReleaseIdentifier && $release !== 'current' && $release !== 'previous') {
				$removableReleases[] = trim($release);
			}
		}
		sort($removableReleases);

		$removeReleases = array_slice($removableReleases, 0, count($removableReleases) - $keepReleases);
		$removeCommand = '';
		foreach ($removeReleases as $removeRelease) {
			$removeCommand .= "rm -rf {$releasesPath}/{$removeRelease};rm -f {$releasesPath}/{$removeRelease}REVISION;";
		}
		if (count($removeReleases) > 0) {
			$deployment->getLogger()->log(($deployment->isDryRun() ? 'Would remove' : 'Removing') . ' releases ' . implode(', ', $removeReleases));
			$this->shell->executeOrSimulate($removeCommand, $node, $deployment);
		} else {
			$deployment->getLogger()->log('No releases to remove', LOG_DEBUG);
		}
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