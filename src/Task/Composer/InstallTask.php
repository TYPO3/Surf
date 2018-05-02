<?php
namespace TYPO3\Surf\Task\Composer;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

/**
 * Installs the composer packages based on a composer.json file in the projects root folder
 */
class InstallTask extends AbstractComposerTask
{
    /**
     * Command to run
     *
     * @var string
     */
    protected $command = 'install';

    /**
     * Arguments for the command
     *
     * @var array
     */
    protected $arguments = array(
        '--no-ansi',
        '--no-interaction',
        '--no-dev',
        '--no-progress',
        '--classmap-authoritative'
    );
}
