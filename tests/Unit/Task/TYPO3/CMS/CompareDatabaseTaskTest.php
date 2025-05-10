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
use TYPO3\Surf\Task\TYPO3\CMS\CompareDatabaseTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class CompareDatabaseTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');
        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): CompareDatabaseTask
    {
        return new CompareDatabaseTask();
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
        $this->expectException(InvalidArgumentException::class);
        $this->task->execute($this->node, $invalidApplication, $this->deployment, []);
    }

    /**
     * @test
     */
    public function noSuitableCliArgumentsGiven(): void
    {
        $this->mockLogger->expects(self::once())->method('warning')->with(self::anything());
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function executeWithoutArgumentsExecutesCompareDatabaseWithoutArguments(): void
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(sprintf("/test -f '\/home\/jdoe\/app\/releases\/%s\/vendor\/bin\/typo3cms'$/", $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted(sprintf("/test -f '\/home\/jdoe\/app\/releases\/%s\/vendor\/bin\/typo3cms'$/", $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted(sprintf("/cd '\/home\/jdoe\/app\/releases\/%s'$/", $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3cms' 'database:updateschema' 'safe'$/");
    }

    /**
     * @test
     */
    public function executeWithAddModeExecutesCompareDatabaseWithDatabaseCompareModeArgument(): void
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'databaseCompareMode' => '*.add'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(sprintf("/test -f '\/home\/jdoe\/app\/releases\/%s\/vendor\/bin\/typo3cms'$/", $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted(sprintf("/test -f '\/home\/jdoe\/app\/releases\/%s\/vendor\/bin\/typo3cms'$/", $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted(sprintf("/cd '\/home\/jdoe\/app\/releases\/%s'$/", $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3cms' 'database:updateschema' '\*\.add'$/");
    }

    /**
     * @test
     */
    public function executeWithAddModeAndArgumentsExecutesCompareDatabaseWithDatabaseCompareModeArgument(): void
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'databaseCompareMode' => '*.add',
            'arguments' => ['--raw']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(sprintf("/test -f '\/home\/jdoe\/app\/releases\/%s\/vendor\/bin\/typo3cms'$/", $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted(sprintf("/test -f '\/home\/jdoe\/app\/releases\/%s\/vendor\/bin\/typo3cms'$/", $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted(sprintf("/cd '\/home\/jdoe\/app\/releases\/%s'$/", $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3cms' 'database:updateschema' '\*\.add' '--raw'$/");
    }
}
