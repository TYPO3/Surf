<?php
namespace TYPO3\Surf\Application\Neos;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Task\Composer\InstallTask;
use TYPO3\Surf\Task\Neos\Flow\CopyConfigurationTask;
use TYPO3\Surf\Task\Neos\Flow\CreateDirectoriesTask;
use TYPO3\Surf\Task\Neos\Flow\MigrateTask;
use TYPO3\Surf\Task\Neos\Flow\PublishResourcesTask;
use TYPO3\Surf\Task\Neos\Flow\SymlinkConfigurationTask;
use TYPO3\Surf\Task\Neos\Flow\SymlinkDataTask;

class Flow extends BaseApplication
{
    /**
     * The production context
     * @var string
     */
    protected $context = 'Production';

    /**
     * The Neos Flow major and minor version of this application
     * @var string
     */
    protected $version = '4.0';

    public const DEFAULT_WEB_DIRECTORY = 'Web';

    public function __construct(string $name = 'Neos Flow')
    {
        parent::__construct($name);

        $this->options = array_merge($this->options, [
            'webDirectory' => self::DEFAULT_WEB_DIRECTORY,
        ]);
    }

    public function registerTasks(Workflow $workflow, Deployment $deployment): void
    {
        parent::registerTasks($workflow, $deployment);

        $workflow
            ->addTask(CreateDirectoriesTask::class, 'initialize', $this)
            ->afterStage('update', [
                SymlinkDataTask::class,
                SymlinkConfigurationTask::class,
                CopyConfigurationTask::class
            ], $this)
            ->addTask(MigrateTask::class, 'migrate', $this)
            ->addTask(PublishResourcesTask::class, 'finalize', $this);
    }

    protected function registerTasksForUpdateMethod(Workflow $workflow, string $updateMethod): void
    {
        switch ($updateMethod) {
            case 'composer':
                $workflow->addTask(InstallTask::class, 'update', $this);
                break;
            default:
                parent::registerTasksForUpdateMethod($workflow, $updateMethod);
                break;
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
    public function buildCommand(string $targetPath, string $command, array $arguments = [], string $phpBinaryPathAndFilename = 'php'): string
    {
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
