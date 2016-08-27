<?php
namespace TYPO3\Surf\Application\TYPO3;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;

/**
 * A Neos Flow application template
 */
class Flow extends \TYPO3\Surf\Application\BaseApplication
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
    protected $version = '3.0';

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = 'Neos Flow')
    {
        parent::__construct($name);
        $this->options = array_merge($this->options, array(
            'updateMethod' => 'composer'
        ));
    }

    /**
     * Register tasks for this application
     *
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @return void
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment)
    {
        parent::registerTasks($workflow, $deployment);

        $workflow
            ->addTask('TYPO3\\Surf\\Task\\TYPO3\\Flow\\CreateDirectoriesTask', 'initialize', $this)
            ->afterStage('update', array(
                'TYPO3\\Surf\\Task\\TYPO3\\Flow\\SymlinkDataTask',
                'TYPO3\\Surf\\Task\\TYPO3\\Flow\\SymlinkConfigurationTask',
                'TYPO3\\Surf\\Task\\TYPO3\\Flow\\CopyConfigurationTask'
            ), $this)
            ->addTask('TYPO3\\Surf\\Task\\TYPO3\\Flow\\MigrateTask', 'migrate', $this)
            ->addTask('TYPO3\\Surf\\Task\\TYPO3\\Flow\\PublishResourcesTask', 'finalize', $this);
    }

    /**
     * Register local composer install task for packageMethod "git" after stage "package"
     *
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
     * @param string $packageMethod
     * @return void
     */
    protected function registerTasksForPackageMethod(Workflow $workflow, $packageMethod)
    {
        parent::registerTasksForPackageMethod($workflow, $packageMethod);

        $workflow->defineTask('TYPO3\\Surf\\DefinedTask\\Composer\\LocalInstallTask', 'TYPO3\\Surf\\Task\\Composer\\InstallTask', array(
            'nodeName' => 'localhost',
            'useApplicationWorkspace' => true
        ));

        if ($packageMethod === 'git') {
            $workflow->afterStage('package', 'TYPO3\\Surf\\DefinedTask\\Composer\\LocalInstallTask', $this);
        }
    }

    /**
     * Add support for updateMethod "composer"
     *
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
     * @param string $updateMethod
     * @return void
     */
    protected function registerTasksForUpdateMethod(Workflow $workflow, $updateMethod)
    {
        switch ($updateMethod) {
            case 'composer':
                $workflow->addTask('TYPO3\\Surf\\Task\\Composer\\InstallTask', 'update', $this);
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
     * @return \TYPO3\Surf\Application\TYPO3\Flow
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
        } else {
            return 'BuildEssentials';
        }
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
        } else {
            return 'flow';
        }
    }
}
