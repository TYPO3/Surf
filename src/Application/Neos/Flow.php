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

/**
 * A Neos Flow application template
 */
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

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = 'Neos Flow')
    {
        parent::__construct($name);
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
                SymlinkDataTask::class,
                SymlinkConfigurationTask::class,
                CopyConfigurationTask::class
            ], $this)
            ->addTask(MigrateTask::class, 'migrate', $this)
            ->addTask(PublishResourcesTask::class, 'finalize', $this);
    }

    /**
     * Add support for updateMethod "composer"
     *
     * @param Workflow $workflow
     * @param string $updateMethod
     */
    protected function registerTasksForUpdateMethod(Workflow $workflow, $updateMethod)
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

    /**
     * Set the application production context
     *
     * @param string $context
     * @return Flow
     */
    public function setContext($context)
    {
        $this->context = trim($context);
        return $this;
    }

    /**
     * Get the application production context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the directory name for build essentials (e.g. to run unit tests)
     *
     * The value depends on the Flow version of the application.
     *
     * @return string
     */
    public function getBuildEssentialsDirectoryName()
    {
        if ($this->getVersion() <= '1.1') {
            return 'Common';
        }
        return 'BuildEssentials';
    }

    /**
     * Get the name of the Flow script (flow or flow3)
     *
     * The value depends on the Flow version of the application.
     *
     * @return string
     */
    public function getFlowScriptName()
    {
        if ($this->getVersion() <= '1.1') {
            return 'flow3';
        }
        return 'flow';
    }

    /**
     * Get the package key to prefix the command
     *
     * @param string $command
     * @return string
     */
    public function getCommandPackageKey($command = '')
    {
        if ($this->getVersion() < '2.0') {
            return 'typo3.flow3';
        }
        if ($this->getVersion() < '4.0') {
            return 'typo3.flow';
        }
        return 'neos.flow';
    }

    /**
     * Returns a executable flow command including the context
     *
     * @param string $targetPath the path where the command should be executed
     * @param string $command the actual command for example `cache:flush`
     * @param array $arguments list of arguments which will be appended to the command
     * @param string $phpBinaryPathAndFilename the path to the php binary
     * @return string
     */
    public function buildCommand($targetPath, $command, array $arguments = [], $phpBinaryPathAndFilename = 'php')
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
