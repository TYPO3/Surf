<?php

namespace TYPO3\Surf;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Service\ShellCommandService;

/**
 * Find all releases for current application
 *
 * @param Deployment $deployment
 * @param Node $node
 * @param Application $application
 * @param ShellCommandService $shell
 *
 * @return array[]|false|string[]
 */
function findAllReleases(Deployment $deployment, Node $node, Application $application, ShellCommandService $shell)
{
    $releasesPath = $application->getReleasesPath();
    $allReleasesList = $shell->execute("if [ -d $releasesPath/. ]; then find $releasesPath/. -maxdepth 1 -type d -exec basename {} \; ; fi", $node, $deployment);

    return preg_split('/\s+/', $allReleasesList, -1, PREG_SPLIT_NO_EMPTY);
}

/**
 * Get previous release identifier
 * @param Deployment $deployment
 * @param Node $node
 * @param Application $application
 * @param ShellCommandService $shell
 *
 * @return string
 */
function findPreviousReleaseIdentifier(Deployment $deployment, Node $node, Application $application, ShellCommandService $shell)
{
    $previousReleasePath = $application->getReleasesPath() . '/previous';
    return trim($shell->execute("if [ -h $previousReleasePath ]; then basename `readlink $previousReleasePath` ; fi", $node, $deployment));
}

/**
 * Get current release identifier
 * @param Deployment $deployment
 * @param Node $node
 * @param Application $application
 * @param ShellCommandService $shell
 *
 * @return string
 * @throws Exception\TaskExecutionException
 */
function findCurrentReleaseIdentifier(Deployment $deployment, Node $node, Application $application, ShellCommandService $shell)
{
    $currentReleasePath = $application->getReleasesPath() . '/current';
    return trim($shell->execute("if [ -h $currentReleasePath ]; then basename `readlink $currentReleasePath` ; fi", $node, $deployment));
}
