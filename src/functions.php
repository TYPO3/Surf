<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf;

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Service\ShellCommandService;

/**
 * Find all releases for current application
 *
 * @return string[]
 */
function findAllReleases(Deployment $deployment, Node $node, Application $application, ShellCommandService $shell): array
{
    $releasesPath = $application->getReleasesPath();
    $allReleasesList = $shell->execute("if [ -d $releasesPath/. ]; then find $releasesPath/. -maxdepth 1 -type d -exec basename {} \; ; fi", $node, $deployment);

    $allReleases = preg_split('/\s+/', $allReleasesList, -1, PREG_SPLIT_NO_EMPTY);

    if ($allReleases === false) {
        return[];
    }

    return $allReleases;
}

/**
 * Get previous release identifier
 *
 * @return string
 */
function findPreviousReleaseIdentifier(Deployment $deployment, Node $node, Application $application, ShellCommandService $shell): string
{
    $previousReleasePath = $application->getReleasesPath() . '/previous';
    return trim($shell->execute("if [ -h $previousReleasePath ]; then basename `readlink $previousReleasePath` ; fi", $node, $deployment) ?? '');
}

/**
 * Get current release identifier
 *
 * @return string
 */
function findCurrentReleaseIdentifier(Deployment $deployment, Node $node, Application $application, ShellCommandService $shell): string
{
    $currentReleasePath = $application->getReleasesPath() . '/current';
    return trim($shell->execute("if [ -h $currentReleasePath ]; then basename `readlink $currentReleasePath` ; fi", $node, $deployment));
}
