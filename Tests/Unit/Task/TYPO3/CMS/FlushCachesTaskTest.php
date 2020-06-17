<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Class SetUpExtensionsTaskTest
 */
class FlushCachesTaskTest extends BaseTaskTest
{
    /**
     * @var FlushCachesTask
     */
    protected $task;

    /**
     * @return FlushCachesTask
     */
    protected function createTask()
    {
        return new FlushCachesTask();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeWithoutOptionExecutesCacheFlushWithForceParameter()
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3cms' 'cache:flush' '--force'$/");
    }

    /**
     * @test
     */
    public function executeWithEmptyArgumentsExecutesCacheFlushWithoutArguments()
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'arguments' => []
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3cms' 'cache:flush'$/");
    }

    /**
     * @test
     */
    public function executeWithFilesOnlyArgumentExecutesCacheFlushWithFilesOnlyArgument()
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'arguments' => ['--files-only']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3cms' 'cache:flush' '--files-only'$/");
    }
}
