<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Application\Neos;

use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Domain\Enum\SimpleWorkflowStage;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Task\Neos\Flow\CopyConfigurationTask;
use TYPO3\Surf\Task\Neos\Flow\CreateDirectoriesTask;
use TYPO3\Surf\Task\Neos\Flow\MigrateTask;
use TYPO3\Surf\Task\Neos\Flow\PublishResourcesTask;
use TYPO3\Surf\Task\Neos\Flow\SymlinkConfigurationTask;
use TYPO3\Surf\Task\Neos\Flow\SymlinkDataTask;
use TYPO3\Surf\Task\Neos\Flow\WarmUpCacheTask;
use TYPO3\Surf\Task\SymlinkReleaseTask;

class Flow extends BaseApplication
{
    /**
     * The production context
     */
    protected string $context = 'Production';

    /**
     * The Neos Flow major and minor version of this application
     */
    protected string $version = '4.0';

    public const DEFAULT_WEB_DIRECTORY = 'Web';

    public function __construct(string $name = 'Neos Flow')
    {
        parent::__construct($name);

        $this->options = array_merge($this->options, [
            'webDirectory' => self::DEFAULT_WEB_DIRECTORY,
            'enableCacheWarmupBeforeSwitchingToNewRelease' => false,
            'enableCacheWarmupAfterSwitchingToNewRelease' => false,
        ]);
    }

    public function registerTasks(Workflow $workflow, Deployment $deployment): void
    {
        parent::registerTasks($workflow, $deployment);

        $workflow
            ->addTask(CreateDirectoriesTask::class, SimpleWorkflowStage::STEP_01_INITIALIZE, $this)
            ->afterStage(SimpleWorkflowStage::STEP_05_UPDATE, [
                SymlinkDataTask::class,
                SymlinkConfigurationTask::class,
                CopyConfigurationTask::class
            ], $this)
            ->addTask(MigrateTask::class, SimpleWorkflowStage::STEP_06_MIGRATE, $this)
            ->addTask(PublishResourcesTask::class, SimpleWorkflowStage::STEP_07_FINALIZE, $this);

        if ($this->provideBoolOption('enableCacheWarmupBeforeSwitchingToNewRelease')) {
            $workflow->addTask(WarmUpCacheTask::class, SimpleWorkflowStage::STEP_07_FINALIZE, $this);
        }
        if ($this->provideBoolOption('enableCacheWarmupAfterSwitchingToNewRelease')) {
            $workflow->afterTask(SymlinkReleaseTask::class, WarmUpCacheTask::class, $this);
        }
    }

    public function setContext(string $context): self
    {
        $this->context = trim($context);
        return $this;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get the directory name for build essentials (e.g. to run unit tests)
     *
     * The value depends on the Flow version of the application.
     */
    public function getBuildEssentialsDirectoryName(): string
    {
        if (version_compare($this->getVersion(), '1.1', '<=')) {
            return 'Common';
        }
        return 'BuildEssentials';
    }

    /**
     * Get the name of the Flow script (flow or flow3)
     *
     * The value depends on the Flow version of the application.
     */
    public function getFlowScriptName(): string
    {
        if (version_compare($this->getVersion(), '1.1', '<=')) {
            return 'flow3';
        }
        return 'flow';
    }

    public function getCommandPackageKey(string $command = ''): string
    {
        if (version_compare($this->getVersion(), '2.0', '<')) {
            return 'typo3.flow3';
        }
        if (version_compare($this->getVersion(), '4.0', '<')) {
            return 'typo3.flow';
        }
        return 'neos.flow';
    }

    /**
     * Returns a executable flow command including the context
     */
    public function buildCommand(
        string $targetPath,
        string $command,
        array $arguments = [],
        string $phpBinaryPathAndFilename = 'php'
    ): string {
        if (strpos($command, '.') === false) {
            $command = $this->getCommandPackageKey($command) . ':' . $command;
        }

        return sprintf(
            'cd %s && FLOW_CONTEXT=%s %s ./%s %s %s',
            $targetPath,
            $this->getContext(),
            $phpBinaryPathAndFilename,
            $this->getFlowScriptName(),
            $command,
            implode(' ', array_map('escapeshellarg', $arguments))
        );
    }
}
