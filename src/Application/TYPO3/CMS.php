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
use TYPO3\Surf\Task\DumpDatabaseTask;
use TYPO3\Surf\Task\RsyncFoldersTask;
use TYPO3\Surf\Task\TYPO3\CMS\CreatePackageStatesTask;
use TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask;
use TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask;
use TYPO3\Surf\Task\TYPO3\CMS\SymlinkDataTask;

class CMS extends BaseApplication
{
    public function __construct(string $name = 'TYPO3 CMS')
    {
        parent::__construct($name);

        $this->options = array_merge($this->options, [
            'context' => 'Production',
            'scriptFileName' => 'vendor/bin/typo3cms',
            'symlinkDataFolders' => [
                'fileadmin',
                'uploads'
            ],
            'rsyncExcludes' => [
                '.ddev',
                '.git',
                '{webDirectory}/fileadmin',
                '{webDirectory}/uploads'
            ]
        ]);
    }

    public function setContext(string $context): self
    {
        $this->options['context'] = trim($context);
        return $this;
    }

    public function getContext(): string
    {
        return $this->options['context'];
    }

    public function registerTasks(Workflow $workflow, Deployment $deployment): void
    {
        parent::registerTasks($workflow, $deployment);

        if ($deployment->hasOption('initialDeployment') && $deployment->getOption('initialDeployment') === true) {
            $workflow->addTask(DumpDatabaseTask::class, 'initialize', $this);
            $workflow->addTask(RsyncFoldersTask::class, 'initialize', $this);
        }
        $workflow
            ->afterStage('transfer', CreatePackageStatesTask::class, $this)
            ->afterStage('update', SymlinkDataTask::class, $this)
            ->afterStage('switch', FlushCachesTask::class, $this)
            ->addTask(SetUpExtensionsTask::class, 'migrate', $this);
    }
}
