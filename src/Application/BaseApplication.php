<?php

namespace TYPO3\Surf\Application;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Task\CleanupReleasesTask;
use TYPO3\Surf\Task\Composer\InstallTask;
use TYPO3\Surf\Task\CreateDirectoriesTask;
use TYPO3\Surf\Task\Generic\CreateDirectoriesTask as GenericCreateDirectoriesTask;
use TYPO3\Surf\Task\Generic\CreateSymlinksTask;
use TYPO3\Surf\Task\GitCheckoutTask;
use TYPO3\Surf\Task\LockDeploymentTask;
use TYPO3\Surf\Task\Package\GitTask;
use TYPO3\Surf\Task\SymlinkReleaseTask;
use TYPO3\Surf\Task\Transfer\RsyncTask;
use TYPO3\Surf\Task\Transfer\ScpTask;
use TYPO3\Surf\Task\UnlockDeploymentTask;

/**
 * A base application with Git checkout and basic release directory structure
 *
 * Most specific applications will extend from BaseApplication.
 */
class BaseApplication extends Application
{
    /**
     * Symlinks, which should be created for each release.
     *
     * @see \TYPO3\Surf\Task\Generic\CreateSymlinksTask
     */
    protected array $symlinks = [];

    /**
     * Directories which should be created on deployment. E.g. shared folders.
     */
    protected array $directories = [];

    /**
     * Basic application specific options
     *
     *   packageMethod: How to prepare the application assets (code and files) locally before transfer
     *
     *     "git" Make a local git checkout and transfer files to the server
     *     none  Default, do not prepare anything locally
     *
     *   transferMethod: How to transfer the application assets to a node
     *
     *     "git" Make a checkout of the application assets remotely on the node
     *
     *   updateMethod: How to prepare and update the application assets on the node after transfer
     *
     *   lockDeployment: Locked deployments can only run once at a time
     */
    protected array $options = [
        'packageMethod' => 'git',
        'transferMethod' => 'rsync',
        'updateMethod' => null,
        'lockDeployment' => true,
        'webDirectory' => self::DEFAULT_WEB_DIRECTORY,
    ];

    /**
     * Register tasks for the base application
     *
     * The base application performs the following tasks:
     *
     * Initialize stage:
     *   - Create directories for release structure
     *
     * Update stage:
     *   - Perform Git checkout (and pass on sha1 / tag or branch option from application to the task)
     *
     * Switch stage:
     *   - Symlink the current and previous release
     *
     * Cleanup stage:
     *   - Clean up old releases
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment): void
    {
        $this->setOption(GenericCreateDirectoriesTask::class . '[directories]', $this->getDirectories());
        $this->setOption(CreateSymlinksTask::class . '[symlinks]', $this->getSymlinks());

        if ($this->hasOption('packageMethod')) {
            $this->registerTasksForPackageMethod($workflow, $this->getOption('packageMethod'));
        }

        if ($this->hasOption('transferMethod')) {
            $this->registerTasksForTransferMethod($workflow, $this->getOption('transferMethod'));
        }

        $workflow->afterStage('transfer', CreateSymlinksTask::class, $this);

        if ($this->hasOption('updateMethod')) {
            $this->registerTasksForUpdateMethod($workflow, (string)$this->getOption('updateMethod'));
        }

        // TODO Define tasks for local shell task and local git checkout

        $workflow
            ->addTask(CreateDirectoriesTask::class, 'initialize', $this)
            ->afterTask(CreateDirectoriesTask::class, GenericCreateDirectoriesTask::class, $this)
            ->addTask(SymlinkReleaseTask::class, 'switch', $this)
            ->addTask(CleanupReleasesTask::class, 'cleanup', $this);

        if ($this->hasOption('lockDeployment') && $this->getOption('lockDeployment') === true) {
            $workflow->addTask(LockDeploymentTask::class, 'lock', $this);
            $workflow->addTask(UnlockDeploymentTask::class, 'unlock', $this);
        }

        if ($deployment->getForceRun()) {
            $workflow->beforeTask(LockDeploymentTask::class, UnlockDeploymentTask::class, $this);
        }
    }

    public function setSymlinks(array $symlinks): self
    {
        $this->symlinks = $symlinks;

        return $this;
    }

    public function getSymlinks(): array
    {
        return $this->symlinks;
    }

    public function addSymlink(string $linkPath, string $sourcePath): self
    {
        $this->symlinks[$linkPath] = $sourcePath;

        return $this;
    }

    public function addSymlinks(array $symlinks): self
    {
        foreach ($symlinks as $linkPath => $sourcePath) {
            $this->addSymlink($linkPath, $sourcePath);
        }

        return $this;
    }

    public function setDirectories(array $directories): self
    {
        $this->directories = $directories;

        return $this;
    }

    public function getDirectories(): array
    {
        return $this->directories;
    }

    public function addDirectory(string $path): self
    {
        $this->directories[] = $path;

        return $this;
    }

    public function addDirectories(array $directories): self
    {
        foreach ($directories as $path) {
            $this->addDirectory($path);
        }

        return $this;
    }

    protected function registerTasksForPackageMethod(Workflow $workflow, ?string $packageMethod): void
    {
        switch ($packageMethod) {
            case 'git':
                $workflow->addTask(GitTask::class, 'package', $this);
                $workflow->defineTask(
                    $localInstallTask = 'TYPO3\\Surf\\DefinedTask\\Composer\\LocalInstallTask',
                    InstallTask::class,
                    [
                        'nodeName' => 'localhost',
                        'useApplicationWorkspace' => true,
                        'additionalArguments' => ['--ignore-platform-reqs'],
                    ]
                );
                $workflow->afterTask(GitTask::class, $localInstallTask, $this);
                break;
        }
    }

    protected function registerTasksForTransferMethod(Workflow $workflow, string $transferMethod): void
    {
        switch ($transferMethod) {
            case 'git':
                $workflow->addTask(GitCheckoutTask::class, 'transfer', $this);
                break;
            case 'rsync':
                $workflow->addTask(RsyncTask::class, 'transfer', $this);
                break;
            case 'scp':
                $workflow->addTask(ScpTask::class, 'transfer', $this);
                break;
        }
    }

    protected function registerTasksForUpdateMethod(Workflow $workflow, string $updateMethod): void
    {
    }
}
