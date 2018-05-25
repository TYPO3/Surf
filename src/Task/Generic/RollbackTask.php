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
use function TYPO3\Surf\findAllReleases;
use function TYPO3\Surf\findCurrentReleaseIdentifier;
use function TYPO3\Surf\findPreviousReleaseIdentifier;

final class RollbackTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $allReleases = findAllReleases($deployment, $node, $application, $this->shell);

        $releasesPath = $application->getReleasesPath();

        $releases = array_map('trim', array_filter($allReleases, function ($release) {
            return $release !== '.' && $release !== 'current' && $release !== 'previous';
        }));
        sort($releases, SORT_NUMERIC | SORT_DESC);

        if (count($releases) > 1) {
            $previousReleaseIdentifier = findPreviousReleaseIdentifier($deployment, $node, $application, $this->shell);
            $currentReleaseIdentifier = findCurrentReleaseIdentifier($deployment, $node, $application, $this->shell);

            // Symlink to old release.
            $deployment->getLogger()->info(($deployment->isDryRun() ? 'Would symlink current to' : 'Symlink current to') . ' release ' . $previousReleaseIdentifier);
            $symlinkCommand = sprintf('cd %1$s && ln -s ./%2$s current', $releasesPath, $previousReleaseIdentifier);
            $deployment->getLogger()->info($symlinkCommand);
            $this->shell->executeOrSimulate($symlinkCommand, $node, $deployment);

            // Remove current release
            $deployment->getLogger()->info(($deployment->isDryRun() ? 'Would remove' : 'Removing') . ' old current release ' . $currentReleaseIdentifier);
            $removeCommand = sprintf('rm -rf %1$s/%2$s; rm -rf %1$s/%2$sREVISION;', $releasesPath, $currentReleaseIdentifier);
            $this->shell->executeOrSimulate($removeCommand, $node, $deployment);
        } else {
            $deployment->getLogger()->notice('No more releases you can revert to.');
        }
    }

    /**
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
