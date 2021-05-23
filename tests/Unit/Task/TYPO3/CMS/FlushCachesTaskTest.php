<?php

namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Prophecy\Argument;
use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class FlushCachesTaskTest extends BaseTaskTest
{
    /**
     * @var FlushCachesTask
     */
    protected $task;

    /**
     * @return FlushCachesTask
     */
    protected function createTask(): FlushCachesTask
    {
        return new FlushCachesTask();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeFlushCacheCommandWithWrongOptionsType(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $options = [
            'scriptFileName' => 'typo3cms',
            'arguments' => 1
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @test
     */
    public function wrongApplicationTypeGivenThrowsException(): void
    {
        $invalidApplication = new BaseApplication('Hello world app');
        $this->expectException(\InvalidArgumentException::class);
        $this->task->execute($this->node, $invalidApplication, $this->deployment, []);
    }

    /**
     * @test
     */
    public function noSuitableCliArgumentsGiven(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment, []);
        $this->mockLogger->warning(Argument::any())->shouldBeCalledOnce();
    }

    /**
     * @test
     */
    public function executeWithoutArgumentsExecutesCacheFlushWithoutArguments(): void
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3cms' 'cache:flush'$/");
    }

    /**
     * @test
     */
    public function executeWithEmptyArgumentsExecutesCacheFlushWithoutArguments(): void
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
    public function executeWithFilesOnlyArgumentExecutesCacheFlushWithFilesOnlyArgument(): void
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'arguments' => ['--files-only']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3cms' 'cache:flush' '--files-only'$/");
    }

    /**
     * @test
     */
    public function executeWithMultipleArgumentExecutesCacheFlushWithArguments(): void
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'arguments' => ['--files-only', '--force']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3cms' 'cache:flush' '--files-only' '--force'$/");
    }
}
