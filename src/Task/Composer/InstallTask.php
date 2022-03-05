<?php

declare(strict_types=1);

namespace TYPO3\Surf\Task\Composer;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

/**
 * Installs the composer packages based on a composer.json file in the projects root folder
 *
 * It takes the following options:
 *
 * * composerCommandPath - The path where composer is located.
 * * nodeName - The name of the node where composer should install the packages.
 * * useApplicationWorkspace (optional) - If true Surf uses the workspace path, else it uses the release path of the application.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Composer\InstallTask', [
 *              'composerCommandPath' => '/usr/bin/composer',
 *              'nodeName' => 'outerSpace',
 *              'useApplicationWorkspace' => 'true'
 *          ]
 *      );
 */
class InstallTask extends AbstractComposerTask
{
    /**
     * Command to run
     */
    protected string $command = 'install';

    /**
     * Arguments for the command
     */
    protected array $arguments = [
        '--no-ansi',
        '--no-interaction',
        '--no-dev',
        '--no-progress',
        '--classmap-authoritative'
    ];
}
