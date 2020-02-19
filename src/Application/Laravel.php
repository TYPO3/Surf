<?php

namespace TYPO3\Surf\Application;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Task\Laravel\ClearAuthResetsTask;
use TYPO3\Surf\Task\Laravel\CreateDirectoriesTask;
use TYPO3\Surf\Task\Laravel\EnvAwareTask;
use TYPO3\Surf\Task\Laravel\FlushCachesTask;
use TYPO3\Surf\Task\Laravel\MigrateTask;
use TYPO3\Surf\Task\Laravel\StorageLinkTask;
use TYPO3\Surf\Task\Laravel\SymlinkDataTask;
use TYPO3\Surf\Task\Laravel\WarmUpCachesTask;

class Laravel extends BaseApplication
{
    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = 'Laravel')
    {
        parent::__construct($name);
        $this->options = array_merge($this->options, [
            'webDirectory' => 'public',
            'directories' => [
                'storage'
            ],
            'rsyncExcludes' => [
                '.git',
                '*.example',
                '*.lock',
                '.editorconfig',
                '.gitattributes',
                '.gitignore',
                '.styleci.yml',
                '/package.json',
                '/package-lock.json',
                '/phpunit.xml',
                '/README.md',
                '/storage',
                '/tests',
                '/server.php',
                '/webpack.mix.js',
                '{webDirectory}/storage',
            ]
        ]);
    }

    /**
     * Register tasks for this application
     *
     * @param Workflow $workflow
     * @param Deployment $deployment
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment)
    {
        parent::registerTasks($workflow, $deployment);

        $workflow
            ->addTask(CreateDirectoriesTask::class, 'initialize', $this)
            ->afterStage('update', [
                EnvAwareTask::class,
                SymlinkDataTask::class
            ], $this)
            ->afterStage('migrate', MigrateTask::class, $this)
            ->afterStage('finalize', [
                FlushCachesTask::class,
                WarmUpCachesTask::class
            ], $this)
            ->afterStage('switch', StorageLinkTask::class, $this)
            ->afterStage('cleanup', ClearAuthResetsTask::class, $this)
        ;
    }
}
