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
     * @var string[]
     */
    protected array $symlinks = [];

    /**
     * Directories which should be created on deployment. E.g. shared folders.
     *
     * @var string[]
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

        $workflow->afterStage(SimpleWorkflowStage::STEP_04_TRANSFER, CreateSymlinksTask::class, $this);

        if ($this->hasOption('updateMethod')) {
            $this->registerTasksForUpdateMethod($workflow, (string)$this->getOption('updateMethod'));
        }

        // TODO Define tasks for local shell task and local git checkout

        $workflow
            ->addTask(CreateDirectoriesTask::class, SimpleWorkflowStage::STEP_01_INITIALIZE, $this)
            ->afterTask(CreateDirectoriesTask::class, GenericCreateDirectoriesTask::class, $this)
            ->addTask(SymlinkReleaseTask::class, SimpleWorkflowStage::STEP_09_SWITCH, $this)
            ->addTask(CleanupReleasesTask::class, SimpleWorkflowStage::STEP_10_CLEANUP, $this);

        if ($this->provideBoolOption('lockDeployment')) {
            $workflow->addTask(LockDeploymentTask::class, SimpleWorkflowStage::STEP_02_LOCK, $this);
            $workflow->addTask(UnlockDeploymentTask::class, SimpleWorkflowStage::STEP_11_UNLOCK, $this);
        }

        if ($deployment->getForceRun()) {
            $workflow->beforeTask(LockDeploymentTask::class, UnlockDeploymentTask::class, $this);
        }
    }

    /**
     * @param string[] $symlinks
     */
    public function setSymlinks(array $symlinks): self
    {
        $this->symlinks = $symlinks;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getSymlinks(): array
    {
        return $this->symlinks;
    }

    public function addSymlink(string $linkPath, string $sourcePath): self
    {
        $this->symlinks[$linkPath] = $sourcePath;

        return $this;
    }

    /**
     * @param string[] $symlinks
     */
    public function addSymlinks(array $symlinks): self
    {
        foreach ($symlinks as $linkPath => $sourcePath) {
            $this->addSymlink($linkPath, $sourcePath);
        }

        return $this;
    }

    /**
     * @param string[] $directories
     */
    public function setDirectories(array $directories): self
    {
        $this->directories = $directories;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    public function addDirectory(string $path): self
    {
        $this->directories[] = $path;

        return $this;
    }

    /**
     * @param string[] $directories
     */
    public function addDirectories(array $directories): self
    {
        foreach ($directories as $path) {
            $this->addDirectory($path);
        }

        return $this;
    }

    protected function registerTasksForPackageMethod(Workflow $workflow, ?string $packageMethod): void
    {
        if ($packageMethod === 'git') {
            $workflow->addTask(GitTask::class, SimpleWorkflowStage::STEP_03_PACKAGE, $this);
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
        }
    }

    protected function registerTasksForTransferMethod(Workflow $workflow, string $transferMethod): void
    {
        switch ($transferMethod) {
            case 'git':
                $workflow->addTask(GitCheckoutTask::class, SimpleWorkflowStage::STEP_04_TRANSFER, $this);
                break;
            case 'rsync':
                $workflow->addTask(RsyncTask::class, SimpleWorkflowStage::STEP_04_TRANSFER, $this);
                break;
            case 'scp':
                $workflow->addTask(ScpTask::class, SimpleWorkflowStage::STEP_04_TRANSFER, $this);
                break;
        }
    }

    protected function registerTasksForUpdateMethod(Workflow $workflow, string $updateMethod): void
    {
        if ($updateMethod === 'composer') {
            $workflow->addTask(InstallTask::class, SimpleWorkflowStage::STEP_05_UPDATE, $this);
        }
    }
}
