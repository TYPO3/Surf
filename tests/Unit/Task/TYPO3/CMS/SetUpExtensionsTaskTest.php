<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Version\VersionCheckerInterface;
use TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class SetUpExtensionsTaskTest extends BaseTaskTest
{
    use ProphecyTrait;
    /**
     * @var ObjectProphecy|VersionCheckerInterface
     */
    private $versionChecker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): SetUpExtensionsTask
    {
        $this->versionChecker = $this->prophesize(VersionCheckerInterface::class);
        $this->versionChecker->isSatisified(Argument::any(), Argument::any())->willReturn(false);
        return new SetUpExtensionsTask($this->versionChecker->reveal());
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
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->node)}'");
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
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->node)}'");
        $this->assertCommandExecuted(
            "test -f '{$this->deployment->getApplicationReleasePath($this->node)}/vendor/bin/typo3cms'"
        );
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' 'foo,bar'");
    }

    /**
     * @test
     */
    public function consoleIsFoundInCorrectPathWithoutAppDirectoryInVersionEqualOrHigherThanSeven(): void
    {
        $this->versionChecker->isSatisified(Argument::any(), Argument::any())->willReturn(true);

        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'extensionKeys' => ['foo', 'bar']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->node)}'");
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' '-e' 'foo' '-e' 'bar'");
    }

    /**
     * @test
     */
    public function consoleIsFoundInCorrectPathWithoutAppDirectoryDefinedWithVersionHigherOrEqualSeven(): void
    {
        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'extensionKeys' => ['foo', 'bar'],
            'scriptFileVersion' => '7.0.0'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->node)}'");
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' '-e' 'foo' '-e' 'bar'");
    }

    /**
     * @test
     */
    public function consoleIsFoundInCorrectPathWithoutAppDirectoryInVersionEqualOrHigherThanSevenButInMultilineFormat(): void
    {
        $this->versionChecker->isSatisified(Argument::any(), Argument::any())->willReturn(true);

        $options = [
            'scriptFileName' => 'vendor/bin/typo3cms',
            'extensionKeys' => ['foo', 'bar']
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("cd '{$this->deployment->getApplicationReleasePath($this->node)}'");
        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setup' '-e' 'foo' '-e' 'bar'");
    }

    /**
     * @test
     */
    public function executeWithoutOptionExecutesSetUpInVersionEqualOrHigherThanSeven(): void
    {
        $this->versionChecker->isSatisified(Argument::any(), Argument::any())->willReturn(true);

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
        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['scriptFileName' => 'vendor/bin/typo3cms']
        );

        $this->assertCommandExecuted("php 'vendor/bin/typo3cms' 'extension:setupactive'");
    }
}
