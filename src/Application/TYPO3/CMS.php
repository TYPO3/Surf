<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Application\TYPO3;

use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Domain\Enum\SimpleWorkflowStage;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Task\DumpDatabaseTask;
use TYPO3\Surf\Task\RsyncFoldersTask;
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
            'typo3CliFileName' => 'vendor/bin/typo3',
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

        if ($deployment->provideBoolOption('initialDeployment')) {
            $workflow->addTask(DumpDatabaseTask::class, SimpleWorkflowStage::STEP_01_INITIALIZE, $this);
            $workflow->addTask(RsyncFoldersTask::class, SimpleWorkflowStage::STEP_01_INITIALIZE, $this);
        }
        $workflow
            ->afterStage(SimpleWorkflowStage::STEP_05_UPDATE, SymlinkDataTask::class, $this)
            ->afterStage(SimpleWorkflowStage::STEP_09_SWITCH, FlushCachesTask::class, $this)
            ->addTask(SetUpExtensionsTask::class, SimpleWorkflowStage::STEP_06_MIGRATE, $this);
    }
}
