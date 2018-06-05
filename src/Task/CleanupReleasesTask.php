<?php
namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * A cleanup task to delete old (unused) releases
 */
class CleanupReleasesTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

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
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!isset($options['keepReleases'])) {
            $deployment->getLogger()->debug(($deployment->isDryRun() ? 'Would keep' : 'Keeping') . ' all releases for "' . $application->getName() . '"');
            return;
        }

        $keepReleases = $options['keepReleases'];
        $releasesPath = $application->getReleasesPath();
        $currentReleaseIdentifier = $deployment->getReleaseIdentifier();
        $previousReleasePath = $application->getReleasesPath() . '/previous';
        $previousReleaseIdentifier = trim($this->shell->execute("if [ -h $previousReleasePath ]; then basename `readlink $previousReleasePath` ; fi", $node, $deployment));

        $allReleasesList = $this->shell->execute("if [ -d $releasesPath/. ]; then find $releasesPath/. -maxdepth 1 -type d -exec basename {} \; ; fi", $node, $deployment);
        $allReleases = preg_split('/\s+/', $allReleasesList, -1, PREG_SPLIT_NO_EMPTY);

        $removableReleases = [];
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
            $deployment->getLogger()->info(($deployment->isDryRun() ? 'Would remove' : 'Removing') . ' releases ' . implode(', ', $removeReleases));
            $this->shell->executeOrSimulate($removeCommand, $node, $deployment);
        } else {
            $deployment->getLogger()->info('No releases to remove');
        }
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
