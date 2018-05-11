<?php
namespace TYPO3\Surf\Application\TYPO3;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;

/**
 * TYPO3 CMS application
 */
class CMS extends BaseApplication
{
    /**
     * Set the application production context
     *
     * @param string $context
     * @return CMS
     */
    public function setContext($context)
    {
        $this->options['context'] = trim($context);
        return $this;
    }

    /**
     * Get the application production context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->options['context'];
    }

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = 'TYPO3 CMS')
    {
        parent::__construct($name);
        $this->options = array_merge($this->options, array(
            'context' => 'Production',
            'scriptFileName' => 'vendor/bin/typo3cms',
            'webDirectory' => 'web',
            'rsyncExcludes' => array(
                '.git',
                'web/fileadmin',
                'web/uploads'
            )
        ));
    }

    /**
     * Register tasks for this application
     *
     * @param Workflow $workflow
     * @param Deployment $deployment
     * @return void
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment)
    {
        parent::registerTasks($workflow, $deployment);

        if ($deployment->hasOption('initialDeployment') && $deployment->getOption('initialDeployment') === true) {
            $workflow->addTask('TYPO3\\Surf\\Task\\DumpDatabaseTask', 'initialize', $this);
            $workflow->addTask('TYPO3\\Surf\\Task\\RsyncFoldersTask', 'initialize', $this);
        }
        $workflow
            ->afterStage('transfer', 'TYPO3\\Surf\\Task\\TYPO3\\CMS\\CreatePackageStatesTask', $this)
            ->afterStage(
                'update',
                array(
                    'TYPO3\\Surf\\Task\\TYPO3\\CMS\\SymlinkDataTask',
                    'TYPO3\\Surf\\Task\\TYPO3\\CMS\\CopyConfigurationTask'
                ),
                $this
            )
            ->afterStage('switch', 'TYPO3\\Surf\\Task\\TYPO3\\CMS\\FlushCachesTask', $this)
            ->addTask('TYPO3\\Surf\\Task\\TYPO3\\CMS\\SetUpExtensionsTask', 'migrate', $this);
    }
}
