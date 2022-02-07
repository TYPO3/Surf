<?php

namespace TYPO3\Surf\Tests\Unit\Application\Neos;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\SimpleWorkflow;
use TYPO3\Surf\Domain\Service\TaskManager;
use TYPO3\Surf\Task\Composer\InstallTask;

class FlowTest extends TestCase
{
    /**
     * @var Flow
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new Flow();
    }

    /**
     * @test
     * @dataProvider commandPackageKeyProvider
     */
    public function getCommandPackageKey(string $version, string $expectedCommandPackageKey): void
    {
        $this->subject->setVersion($version);
        self::assertSame($expectedCommandPackageKey, $this->subject->getCommandPackageKey());
    }

    /**
     * @test
     * @dataProvider essentialsDirectoryNameProvider
     */
    public function getBuildEssentialsDirectoryName(string $version, string $expectedEssentialsDirectoryName): void
    {
        $this->subject->setVersion($version);
        self::assertSame($expectedEssentialsDirectoryName, $this->subject->getBuildEssentialsDirectoryName());
    }

    /**
     * @test
     * @dataProvider flowScriptNameProvider
     */
    public function getFlowScriptName(string $version, string $expectedFlowScriptName): void
    {
        $this->subject->setVersion($version);
        self::assertSame($expectedFlowScriptName, $this->subject->getFlowScriptName());
    }

    /**
     * @test
     */
    public function registerComposerInstallTask(): void
    {
        $deployment = $this->createDeployment();
        $workflow = new SimpleWorkflow($this->prophesize(TaskManager::class)->reveal());
        $this->subject->setOption('updateMethod', 'composer');
        $this->subject->registerTasks($workflow, $deployment->reveal());
        $tasks = $workflow->getTasks();
        self::assertContains(InstallTask::class, $tasks['stage'][$this->subject->getName()]['update']['tasks']);
    }

    public function commandPackageKeyProvider(): array
    {
        return [
            ['2.0', 'typo3.flow'],
            ['3.8', 'typo3.flow'],
            ['4.0', 'neos.flow'],
        ];
    }

    public function flowScriptNameProvider(): array
    {
        return [
            ['1.0', 'flow3'],
            ['1.1', 'flow3'],
            ['1.2', 'flow']
        ];
    }

    public function essentialsDirectoryNameProvider(): array
    {
        return [
            ['1.0', 'Common'],
            ['1.1', 'Common'],
            ['1.2', 'BuildEssentials']
        ];
    }

    /**
     * @return Deployment|ObjectProphecy
     */
    private function createDeployment()
    {
        $deployment = $this->prophesize(Deployment::class);
        $deployment->getForceRun()->willReturn(false);

        return $deployment;
    }
}
