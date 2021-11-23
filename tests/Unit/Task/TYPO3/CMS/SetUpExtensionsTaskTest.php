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

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeWithoutOptionExecutesSetUpActive(): void
    {
        $mockTask = $this->getMockTaskForConsoleVersion('7.0.0');
        $mockTask->execute($this->node, $this->application, $this->deployment, ['scriptFileName' => 'vendor/bin/typo3cms']);
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup'");
    }

    /**
     * @test
     */
    public function executeWithoutOptionExecutesSetUpActiveForOldConsoleVersion(): void
    {
        $mockTask = $this->getMockTaskForConsoleVersion('6.9.9');
        $mockTask->execute($this->node, $this->application, $this->deployment, ['scriptFileName' => 'vendor/bin/typo3cms']);
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

        $mockTask = $this->getMockTaskForConsoleVersion('7.0.0');
        $mockTask->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' '-e' 'foo' '-e' 'bar'");
    }

    /**
     * @test
     */
    public function executeWithOptionExecutesSetUpWithOptionForOldConsoleVersion(): void
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'extensionKeys' => ['foo', 'bar']
        ];

        $mockTask = $this->getMockTaskForConsoleVersion('6.9.9');
        $mockTask->execute($this->node, $this->application, $this->deployment, $options);
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

        $mockTask = $this->getMockTaskForConsoleVersion('7.0.0');
        $mockTask->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->application)}'");
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' '-e' 'foo' '-e' 'bar'");
    }

    /**
     * @test
     */
    public function consoleIsFoundInCorrectPathWithoutAppDirectoryForOldConsoleVersion(): void
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'extensionKeys' => ['foo', 'bar']
        ];

        $mockTask = $this->getMockTaskForConsoleVersion('6.9.9');
        $mockTask->execute($this->node, $this->application, $this->deployment, $options);
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

        $mockTask = $this->getMockTaskForConsoleVersion('7.0.0');
        $mockTask->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->application)}'");
        $this->assertCommandExecuted(
            "test -f '{$this->deployment->getApplicationReleasePath($this->application)}/vendor/bin/typo3cms'"
        );
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' '-e' 'foo' '-e' 'bar'");
    }

    /**
     * @test
     */
    public function consoleIsFoundInCorrectPathWithWebDirectoryAndSlashesAreTrimmedForOldConsoleVersion(): void
    {
        $options = [
            'extensionKeys' => ['foo', 'bar'],
            'scriptFileName' => 'vendor/bin/typo3cms',
            'webDirectory' => '/web/',
        ];

        $mockTask = $this->getMockTaskForConsoleVersion('6.9.9');
        $mockTask->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->application)}'");
        $this->assertCommandExecuted(
            "test -f '{$this->deployment->getApplicationReleasePath($this->application)}/vendor/bin/typo3cms'"
        );
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' 'foo,bar'");
    }

    protected function getMockShell()
    {
        $commands = &$this->commands;
        $mockShell = $this->getMockBuilder(\TYPO3\Surf\Domain\Service\ShellCommandService::class)->getMock();
        $mockShell
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function ($command) use (&$commands, &$responses) {
                if (is_array($command)) {
                    $commands['executed'] = array_merge($commands['executed'], $command);
                } else {
                    $commands['executed'][] = $command;
                    if (isset($responses[$command])) {
                        return $responses[$command];
                    }
                }
                return '';
            }));
        $mockShell
            ->expects(self::any())
            ->method('executeOrSimulate')
            ->will($this->returnCallback(function ($command) use (&$commands, &$responses) {
                if (is_array($command)) {
                    $commands['executed'] = array_merge($commands['executed'], $command);
                } else {
                    $commands['executed'][] = $command;
                    if (isset($responses[$command])) {
                        return $responses[$command];
                    }
                }
                return '';
            }));
        return $mockShell;
    }

    protected function getMockTaskForConsoleVersion(string $consoleVersion)
    {
        $mockTask = $this->getMockBuilder(\TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask::class)
            ->setMethods(['getConsoleVersion'])->getMock();
        $mockTask->expects($this->once())->method('getConsoleVersion')->willReturn($consoleVersion);
        $mockTask->setShellCommandService($this->getMockShell());
        return $mockTask;
    }
}
