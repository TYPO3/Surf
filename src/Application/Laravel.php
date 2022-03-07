<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Application;

use TYPO3\Surf\Domain\Enum\SimpleWorkflowStage;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Task\Laravel\ClearAuthResetsTask;
use TYPO3\Surf\Task\Laravel\ConfigCacheTask;
use TYPO3\Surf\Task\Laravel\CreateDirectoriesTask;
use TYPO3\Surf\Task\Laravel\EnvAwareTask;
use TYPO3\Surf\Task\Laravel\MigrateTask;
use TYPO3\Surf\Task\Laravel\RouteCacheTask;
use TYPO3\Surf\Task\Laravel\StorageLinkTask;
use TYPO3\Surf\Task\Laravel\SymlinkStorageTask;
use TYPO3\Surf\Task\Laravel\ViewCacheTask;

class Laravel extends BaseApplication
{
    public function __construct(string $name = 'Laravel')
    {
        parent::__construct($name);
        $this->options = array_merge($this->options, [
            'webDirectory' => 'public',
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
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment): void
    {
        parent::registerTasks($workflow, $deployment);

        $workflow
            ->addTask(CreateDirectoriesTask::class, SimpleWorkflowStage::STEP_01_INITIALIZE, $this)
            ->addTask([
                SymlinkStorageTask::class,
                EnvAwareTask::class,
            ], SimpleWorkflowStage::STEP_05_UPDATE, $this)
            ->addTask(MigrateTask::class, SimpleWorkflowStage::STEP_06_MIGRATE, $this)
            ->addTask([
                StorageLinkTask::class,
                ConfigCacheTask::class,
                RouteCacheTask::class,
                ViewCacheTask::class,
            ], SimpleWorkflowStage::STEP_07_FINALIZE, $this)
        ;
    }
}
