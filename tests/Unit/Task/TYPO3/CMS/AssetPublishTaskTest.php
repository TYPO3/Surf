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
use TYPO3\Surf\Task\TYPO3\CMS\AssetPublishTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class AssetPublishTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): AssetPublishTask
    {
        return new AssetPublishTask();
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
    public function missingTypo3CliFailsTheDeploymentInsteadOfSkippingThePublishing(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function executeWithWrongArgumentsTypeThrowsException(): void
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
    public function executeWithoutArgumentsExecutesAssetPublish(): void
    {
        $options = [
            'typo3CliFileName' => 'vendor/bin/typo3'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/^cd '\/releases\/[0-9]+'$/");
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3' 'asset:publish'$/");
    }

    /**
     * @test
     */
    public function executeWithArgumentsPassesThemToAssetPublish(): void
    {
        $options = [
            'typo3CliFileName' => 'vendor/bin/typo3',
            'arguments' => ['-v']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/php 'vendor\/bin\/typo3' 'asset:publish' '-v'$/");
    }

    /**
     * @test
     */
    public function executeWithContextOptionSetsTypo3Context(): void
    {
        $options = [
            'typo3CliFileName' => 'vendor/bin/typo3',
            'context' => 'Production'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/TYPO3_CONTEXT='Production' php 'vendor\/bin\/typo3' 'asset:publish'$/");
    }
}
