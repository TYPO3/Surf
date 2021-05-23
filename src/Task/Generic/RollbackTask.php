<?php
namespace TYPO3\Surf\Task\Generic;

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

final class RollbackTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $allReleases = \TYPO3\Surf\findAllReleases($deployment, $node, $application, $this->shell);

        $releasesPath = $node->getReleasesPath();

        $releases = array_map('trim', array_filter($allReleases, function ($release) {
            return $release !== '.' && $release !== 'current' && $release !== 'previous';
        }));

        sort($releases, SORT_NUMERIC | SORT_DESC);

        $numberOfReleases = count($releases);
        if ($numberOfReleases > 1) {
            $previousReleaseIdentifier = \TYPO3\Surf\findPreviousReleaseIdentifier($deployment, $node, $application, $this->shell);
            $currentReleaseIdentifier = \TYPO3\Surf\findCurrentReleaseIdentifier($deployment, $node, $application, $this->shell);

            // Symlink to old release.
            $deployment->getLogger()->info(($deployment->isDryRun() ? 'Would symlink current to' : 'Symlink current to') . ' release ' . $previousReleaseIdentifier);
            $symlinkCommand = sprintf('cd %1$s && ln -sfn ./%2$s current', $releasesPath, $previousReleaseIdentifier);
            $deployment->getLogger()->info($symlinkCommand);
            $this->shell->executeOrSimulate($symlinkCommand, $node, $deployment);

            // Remove current release
            $deployment->getLogger()->info(($deployment->isDryRun() ? 'Would remove' : 'Removing') . ' old current release ' . $currentReleaseIdentifier);
            $removeCommand = sprintf('rm -rf %1$s/%2$s; rm -rf %1$s/%2$sREVISION;', $releasesPath, $currentReleaseIdentifier);
            $this->shell->executeOrSimulate($removeCommand, $node, $deployment);

            if ($numberOfReleases > 2) {
                list($penultimateRelease) = array_slice($releases, -3, 1);
                // Symlink previous to penultimate release
                $deployment->getLogger()->info(($deployment->isDryRun() ? 'Would symlink previous to' : 'Symlink previous to') . ' release ' . $penultimateRelease);
                $symlinkCommand = sprintf('cd %1$s && ln -sfn ./%2$s previous', $releasesPath, $penultimateRelease);
                $deployment->getLogger()->info($symlinkCommand);
                $this->shell->executeOrSimulate($symlinkCommand, $node, $deployment);
            } else {
                // Remove previous symlink
                $removeCommand = sprintf('rm -rf %1$s/previous', $node->getReleasesPath());
                $deployment->getLogger()->info(($deployment->isDryRun() ? 'Would remove' : 'Removing') . ' previous symlink: ' . $removeCommand);
                $this->shell->executeOrSimulate($removeCommand, $node, $deployment);
            }
        } else {
            $deployment->getLogger()->notice('No more releases you can revert to.');
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
