<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Class SetUpExtensionsTaskTest
 */
class SetUpExtensionsTaskTest extends BaseTaskTest
{
    /**
     * @var SetUpExtensionsTask
     */
    protected $task;

    /**
     * @return SetUpExtensionsTask
     */
    protected function createTask()
    {
        return new SetUpExtensionsTask();
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
    public function executeWithoutOptionExecutesSetUpActive()
    {
        $this->task->execute($this->node, $this->application, $this->deployment, ['scriptFileName' => 'vendor/bin/typo3cms']);
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setupactive'");
    }

    /**
     * @test
     */
    public function executeWithOptionExecutesSetUpWithOption()
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'extensionKeys' => ['foo', 'bar']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' 'foo,bar'");
    }

    /**
     * @test
     */
    public function consoleIsFoundInCorrectPathWithoutAppDirectory()
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'extensionKeys' => ['foo', 'bar']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->application)}'");
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' 'foo,bar'");
    }

    /**
     * @test
     */
    public function consoleIsFoundInCorrectPathWithWebDirectoryAndSlashesAreTrimmed()
    {
        $options = [
            'extensionKeys' => ['foo', 'bar'],
            'scriptFileName' => 'vendor/bin/typo3cms',
            'webDirectory' => '/web/',
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->application)}'");
        $this->assertCommandExecuted("test -f '{$this->deployment->getApplicationReleasePath($this->application)}/vendor/bin/typo3cms'");
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' 'foo,bar'");
    }
}
