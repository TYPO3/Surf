<?php
declare(strict_types=1);

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
use TYPO3\Surf\Task\Laravel\SymlinkStorageTask;
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
     * @return void
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment): void
    {
        parent::registerTasks($workflow, $deployment);

        $workflow
            ->addTask(CreateDirectoriesTask::class, 'initialize', $this)
            ->addTask([
                EnvAwareTask::class,
                SymlinkStorageTask::class
            ], 'update', $this)
            ->addTask(MigrateTask::class, 'migrate', $this)
            ->addTask([
                FlushCachesTask::class,
                WarmUpCachesTask::class
            ], 'finalize', $this)
            ->afterStage('switch', StorageLinkTask::class, $this)
            ->afterStage('cleanup', ClearAuthResetsTask::class, $this)
        ;
    }
}
