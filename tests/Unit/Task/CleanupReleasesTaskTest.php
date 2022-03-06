<?php

declare(strict_types=1);

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use TYPO3\Surf\Domain\Clock\ClockInterface;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Service\ShellCommandService;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Task\CleanupReleasesTask;
use TYPO3\Surf\Tests\Unit\KernelAwareTrait;

class CleanupReleasesTaskTest extends BaseTaskTest
{
    use KernelAwareTrait;

    /**
     * @var MockObject|ShellCommandService $shellCommandService
     */
    private $shellCommandService;

    private array $folderStructure;

    /**
     * @var ClockInterface|ObjectProphecy
     */
    private $clockMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shellCommandService = $this->createMock(ShellCommandService::class);
        $this->task = $this->createTask();
        if ($this->task instanceof ShellCommandServiceAwareInterface) {
            $this->task->setShellCommandService($this->shellCommandService);
        }

        $this->node = new Node('TestNode');
        $this->node->setHostname('hostname');

        $this->deployment = new Deployment('TestDeployment');
        $this->deployment->setContainer(static::getKernel()->getContainer());

        /** @var LoggerInterface|MockObject $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);
        $this->deployment->setLogger($mockLogger);
        $this->deployment->setWorkspacesBasePath('./Data/Surf');
        $this->application = new Application('TestApplication');

        $this->deployment->initialize();

        $this->deployment->setDeploymentBasePath('root');
        $this->folderStructure = [
            '.' => '.',
            '20171108132211' => [
                'index.php',
            ],
            '20171109193135' => [
                'index.php',
            ],
            '20171123223239' => [
                'index.php',
            ],
        ];
    }

    /**
     * @test
     */
    public function doNothingJustLogDebugIfOptionKeepReleasesIsNotDefined(): void
    {
        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->deployment->getLogger();
        $logger->expects(self::once())->method('debug');

        self::assertNull($this->task->execute($this->node, $this->application, $this->deployment, []));
    }

    /**
     * @test
     */
    public function removeReleases(): void
    {
        $folders = array_keys($this->folderStructure);
        $this->shellCommandService->expects(self::at(1))->method('execute')->willReturn(implode(' ', $folders));

        $command = array_reduce(
            ['20171108132211', '20171109193135'],
            fn ($carry, $folder): string => $carry . sprintf(
                'rm -rf %1$s/%2$s;rm -f %1$s/%2$sREVISION;',
                $this->application->getReleasesPath(),
                $folder
            ),
            ''
        );

        $this->shellCommandService->expects(self::once())->method('executeOrSimulate')->with($command);
        $this->task->execute($this->node, $this->application, $this->deployment, ['keepReleases' => 1]);
    }

    /**
     * @test
     * @dataProvider keepReleasesByAgeDataProvider
     *
     * @param int $currentTime
     * @param array $identifiers
     * @param string $stringToTime
     * @param array $expectedFoldersToBeRemoved
     */
    public function removeReleasesByAge(
        $currentTime,
        array $identifiers,
        $stringToTime,
        array $expectedFoldersToBeRemoved
    ): void {
        $folderStructure = [];
        $this->clockMock->currentTime()->willReturn($currentTime);

        $folderStructure['.'] = '.';
        foreach ($identifiers as $time) {
            $timestampForCurrentFolder = strtotime($time, $currentTime);
            $folderName = strftime('%Y%m%d%H%M%S', $timestampForCurrentFolder);
            $this->clockMock->createTimestampFromFormat('YmdHis', $folderName)->willReturn($timestampForCurrentFolder);
            $folderStructure[$folderName] = ['index.php'];
        }

        $this->clockMock->stringToTime(Argument::type('string'))->willReturn(strtotime($stringToTime, $currentTime));

        $folders = array_keys($folderStructure);

        $this->shellCommandService->expects(self::at(1))->method('execute')->willReturn(implode(' ', $folders));

        $command = array_reduce(
            array_map(
                fn ($expectedFolderToBeRemoved) => strftime('%Y%m%d%H%M%S', strtotime($expectedFolderToBeRemoved, $currentTime)),
                $expectedFoldersToBeRemoved
            ),
            fn ($command, $folder): string => $command . sprintf(
                'rm -rf %1$s/%2$s;rm -f %1$s/%2$sREVISION;',
                $this->application->getReleasesPath(),
                $folder
            ),
            ''
        );

        $this->shellCommandService->expects(self::once())->method('executeOrSimulate')->with($command);

        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['onlyRemoveReleasesOlderThan' => 'foo']
        );
    }

    /**
     * @return array
     */
    public function keepReleasesByAgeDataProvider(): array
    {
        return [
            // Remove folders older than 121 seconds
            [
                1535191400,
                [
                    '0 seconds ago',
                    '60 seconds ago',
                    '120 seconds ago',
                    '180 seconds ago',
                ],
                '121 seconds ago',
                ['180 seconds ago'],
            ],
            // Remove folders older than 2 days
            [
                1535191400,
                [
                    '1 second ago',
                    '10 minutes ago',
                    '2 days 1 second ago',
                    '3 days ago',
                ],
                '2 days ago',
                ['2 days 1 second ago', '3 days ago'],
            ],
        ];
    }

    /**
     * @return CleanupReleasesTask
     */
    protected function createTask(): CleanupReleasesTask
    {
        $this->clockMock = $this->prophesize(ClockInterface::class);

        return new CleanupReleasesTask($this->clockMock->reveal());
    }
}
