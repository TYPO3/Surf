<?php
namespace TYPO3\Surf\Task\Composer;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

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
