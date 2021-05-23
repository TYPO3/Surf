<?php

namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Clock\ClockInterface;
use TYPO3\Surf\Domain\Clock\SystemClock;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * A cleanup task to delete old (unused) releases.
 *
 * Cleanup old releases by listing all releases and keeping a configurable
 * number of old releases (application option "keepReleases"). The current
 * and previous release (if one exists) are protected from removal.
 *
 * Note: There is no rollback for this cleanup, so we have to be sure not to delete any
 *       live or referenced releases.
 *
 * It takes the following options:
 *
 * * keepReleases - The number of releases to keep.
 * * onlyRemoveReleasesOlderThanXSeconds - Remove only those releases older than the defined seconds
 *
 * Example configuration:
 *     $application->setOption('keepReleases', 2);
 *     $application->setOption('onlyRemoveReleasesOlderThan', '121 seconds ago')
 * Note: There is no rollback for this cleanup, so we have to be sure not to delete any
 *       live or referenced releases.
 */
class CleanupReleasesTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @var ClockInterface|SystemClock|null
     */
    private $clock;

    public function __construct(ClockInterface $clock)
    {
        $this->clock = $clock;
    }

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (! isset($options['keepReleases']) && ! isset($options['onlyRemoveReleasesOlderThan'])) {
            $deployment->getLogger()->debug(($deployment->isDryRun() ? 'Would keep' : 'Keeping') . ' all releases for "' . $application->getName() . '"');

            return;
        }

        $releasesPath = $node->getReleasesPath();
        $currentReleaseIdentifier = $deployment->getReleaseIdentifier();

        $previousReleaseIdentifier = \TYPO3\Surf\findPreviousReleaseIdentifier($deployment, $node, $application, $this->shell);
        $allReleases = \TYPO3\Surf\findAllReleases($deployment, $node, $application, $this->shell);

        $removableReleases = array_map('trim', array_filter($allReleases, static function ($release) use ($currentReleaseIdentifier, $previousReleaseIdentifier) {
            return $release !== '.' && $release !== $currentReleaseIdentifier && $release !== $previousReleaseIdentifier && $release !== 'current' && $release !== 'previous';
        }));

        if (isset($options['onlyRemoveReleasesOlderThan'])) {
            $removeReleases = $this->removeReleasesByAge($options, $removableReleases);
        } else {
            $removeReleases = $this->removeReleasesByNumber($options, $removableReleases);
        }

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
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * @return array
     */
    private function removeReleasesByAge(array $options, array $removableReleases)
    {
        $onlyRemoveReleasesOlderThan = $this->clock->stringToTime($options['onlyRemoveReleasesOlderThan']);
        $currentTime = $this->clock->currentTime();
        return array_filter($removableReleases, function ($removeRelease) use ($onlyRemoveReleasesOlderThan, $currentTime) {
            return ($currentTime - $this->clock->createTimestampFromFormat('YmdHis', $removeRelease)) > ($currentTime - $onlyRemoveReleasesOlderThan);
        });
    }

    /**
     * @return array
     */
    private function removeReleasesByNumber(array $options, array $removableReleases)
    {
        sort($removableReleases);
        $keepReleases = $options['keepReleases'];
        return array_slice($removableReleases, 0, count($removableReleases) - $keepReleases);
    }
}
