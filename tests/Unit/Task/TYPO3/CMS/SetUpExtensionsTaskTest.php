<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class SetUpExtensionsTaskTest extends BaseTaskTest
{
    /**
     * @var SetUpExtensionsTask
     */
    protected $task;

    /**
     * @return SetUpExtensionsTask
     */
    protected function createTask(): SetUpExtensionsTask
    {
        return new SetUpExtensionsTask();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
        $this->expectTypo3ConsoleVersion('TYPO3 Console 5.8.6');
    }

    /**
     * @test
     */
    public function executeWithoutOptionExecutesSetUpActive(): void
    {
        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['scriptFileName' => 'vendor/bin/typo3cms']
        );

        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setupactive'");
    }

    /**
     * @test
     */
    public function executeWithOptionExecutesSetUpWithOption(): void
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
    public function consoleIsFoundInCorrectPathWithoutAppDirectory(): void
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
    public function consoleIsFoundInCorrectPathWithWebDirectoryAndSlashesAreTrimmed(): void
    {
        $options = [
            'extensionKeys' => ['foo', 'bar'],
            'scriptFileName' => 'vendor/bin/typo3cms',
            'webDirectory' => '/web/',
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->application)}'");
        $this->assertCommandExecuted(
            "test -f '{$this->deployment->getApplicationReleasePath($this->application)}/vendor/bin/typo3cms'"
        );
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' 'foo,bar'");
    }

    /**
     * @test
     */
    public function consoleIsFoundInCorrectPathWithoutAppDirectoryInVersionEqualOrHigherThanSeven(): void
    {
        $this->expectTypo3ConsoleVersion('TYPO3 Console 7.0.0');

        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'extensionKeys' => ['foo', 'bar']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->application)}'");
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' '-e' 'foo' '-e' 'bar'");
    }

    /**
     * @test
     */
    public function consoleIsFoundInCorrectPathWithoutAppDirectoryInVersionEqualOrHigherThanSevenButInMultilineFormat(): void
    {
        $this->expectTypo3ConsoleVersion("TYPO3 Console 7.0.5\nTYPO3 CMS 11.5.7 (Application Context: Production)");

        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'extensionKeys' => ['foo', 'bar']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->application)}'");
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' '-e' 'foo' '-e' 'bar'");
    }

    /**
     * @test
     */
    public function executeWithoutOptionExecutesSetUpInVersionEqualOrHigherThanSeven(): void
    {
        $this->expectTypo3ConsoleVersion('TYPO3 Console 7.0.0');

        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['scriptFileName' => 'vendor/bin/typo3cms']
        );

        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup'");
    }

    /**
     * @test
     */
    public function executeWithoutOptionAndMissingVersionExecutesSetUpActive(): void
    {
        $this->expectTypo3ConsoleVersion('');

        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['scriptFileName' => 'vendor/bin/typo3cms']
        );

        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setupactive'");
    }

    private function expectTypo3ConsoleVersion(string $typo3ConsoleVersion): void
    {
        $versionCommand = 'php \'vendor/bin/typo3cms\' \'--version\'';
        $this->commands['versionCommand'] = $versionCommand;
        $this->responses[$versionCommand] = $typo3ConsoleVersion;
    }
}
