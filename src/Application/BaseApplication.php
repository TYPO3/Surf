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
     * @var array
     */
    protected $symlinks = [];

    /**
     * Directories which should be created on deployment. E.g. shared folders.
     *
     * @var array
     */
    protected $directories = [];

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
     *
     * @var array
     */
    protected $options = [
        'packageMethod' => 'git',
        'transferMethod' => 'rsync',
        'updateMethod' => null,
        'lockDeployment' => true,
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
     *
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment)
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
            $this->registerTasksForUpdateMethod($workflow, $this->getOption('updateMethod'));
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

    /**
     * Override all symlinks to be created with the given array of symlinks.
     *
     * @param array $symlinks
     *
     * @return \TYPO3\Surf\Application\BaseApplication
     * @see addSymlinks()
     */
    public function setSymlinks(array $symlinks)
    {
        $this->symlinks = $symlinks;

        return $this;
    }

    /**
     * Get all symlinks to be created for the application
     *
     * @return array
     */
    public function getSymlinks()
    {
        return $this->symlinks;
    }

    /**
     * Register an additional symlink to be created for the application
     *
     * @param string $linkPath The link to create
     * @param string $sourcePath The file/directory where the link should point to
     *
     * @return \TYPO3\Surf\Application\BaseApplication
     */
    public function addSymlink($linkPath, $sourcePath)
    {
        $this->symlinks[$linkPath] = $sourcePath;

        return $this;
    }

    /**
     * Register an array of additional symlinks to be created for the application
     *
     * @param array $symlinks
     *
     * @return \TYPO3\Surf\Application\BaseApplication
     * @see setSymlinks()
     */
    public function addSymlinks(array $symlinks)
    {
        foreach ($symlinks as $linkPath => $sourcePath) {
            $this->addSymlink($linkPath, $sourcePath);
        }

        return $this;
    }

    /**
     * Override all directories to be created for the application
     *
     * @param array $directories
     *
     * @return \TYPO3\Surf\Application\BaseApplication
     * @see addDIrectories()
     */
    public function setDirectories(array $directories)
    {
        $this->directories = $directories;

        return $this;
    }

    /**
     * Get directories to be created for the application
     *
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    /**
     * Register an additional directory to be created for the application
     *
     * @param string $path
     *
     * @return \TYPO3\Surf\Application\BaseApplication
     */
    public function addDirectory($path)
    {
        $this->directories[] = $path;

        return $this;
    }

    /**
     * Register an array of additional directories to be created for the application
     *
     * @param array $directories
     *
     * @return \TYPO3\Surf\Application\BaseApplication
     * @see setDirectories()
     */
    public function addDirectories(array $directories)
    {
        foreach ($directories as $path) {
            $this->addDirectory($path);
        }

        return $this;
    }

    /**
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
     * @param string $packageMethod
     */
    protected function registerTasksForPackageMethod(Workflow $workflow, $packageMethod)
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

    /**
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
     * @param string $transferMethod
     */
    protected function registerTasksForTransferMethod(Workflow $workflow, $transferMethod)
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

    /**
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
     * @param string $updateMethod
     */
    protected function registerTasksForUpdateMethod(Workflow $workflow, $updateMethod)
    {
    }
}
