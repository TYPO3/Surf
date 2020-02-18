<?php
declare(strict_types = 1);

namespace TYPO3\Surf\Integration;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Action;
use SebastianFeldmann\Git\Repository;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use TYPO3\Flow\Utility\Files;

final class GenerateDocumentationAction implements Action
{

    /**
     * @inheritDoc
     */
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $pathToDocumentation = __DIR__ . '/../Documentation/';
        $pathToApiReference = $pathToDocumentation . '/ApiReference';
        $pathToApiReferenceToBeRemoved = $pathToDocumentation . '/ApiReference/TYPO3';
        $pathToGeneratedFiles = $pathToApiReference . '/TYPO3/Surf/*';

        Files::emptyDirectoryRecursively($pathToApiReference);

        $process = new Process([
            './vendor/bin/sphpdox',
            'process',
            'TYPO3\Surf',
            'src',
            '--output',
            'Documentation/ApiReference',
            '--exclude',
            'TYPO3\\Surf\\Cli;TYPO3\\Surf\\Integration;TYPO3\\Surf\\Command',
            '--title',
            'ApiReference',
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $process = new Process([
            'mv',
            $pathToGeneratedFiles,
            $pathToApiReference
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        Files::removeDirectoryRecursively($pathToApiReferenceToBeRemoved);
    }
}
