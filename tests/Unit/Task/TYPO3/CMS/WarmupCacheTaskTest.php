<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

use InvalidArgumentException;
use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\TYPO3\CMS\WarmupCacheTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class WarmupCacheTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): WarmupCacheTask
    {
        return new WarmupCacheTask();
    }

    /**
     * @test
     */
    public function executeFlushCacheCommandWithWrongOptionsType(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $options = [
            'typo3CliFileName' => 'typo3',
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
        $this->expectException(InvalidArgumentException::class);
        $this->task->execute($this->node, $invalidApplication, $this->deployment, []);
    }

    /**
     * @test
     */
    public function noSuitableCliArgumentsGiven(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function executeWithoutArgumentsExecutesCacheFlushWithoutArguments(): void
    {
        $options = [
            'typo3CliFileName' => 'vendor/bin/typo3'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3' 'cache:warmup'$/");
    }

    /**
     * @test
     */
    public function executeWithEmptyArgumentsExecutesCacheFlushWithoutArguments(): void
    {
        $options = [
            'typo3CliFileName' => 'vendor/bin/typo3',
            'arguments' => []
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3' 'cache:warmup'$/");
    }

    /**
     * @test
     */
    public function executeWithFilesOnlyArgumentExecutesCacheFlushWithFilesOnlyArgument(): void
    {
        $options = [
            'typo3CliFileName' => 'vendor/bin/typo3',
            'arguments' => ['--group="pages"']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3' 'cache:warmup' '--group=\"pages\"'$/");
    }

    /**
     * @test
     */
    public function executeWithMultipleArgumentExecutesCacheFlushWithArguments(): void
    {
        $options = [
            'typo3CliFileName' => 'vendor/bin/typo3',
            'arguments' => ['--group="pages"', '--quiet']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3' 'cache:warmup' '--group=\"pages\"' '--quiet'$/");
    }
}
