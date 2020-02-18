<?php

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Psr\Log\LoggerInterface;
use TYPO3\Surf\Domain\Clock\ClockInterface;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Service\ShellCommandService;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Task\CleanupReleasesTask;

class CleanupReleasesTaskTest extends BaseTaskTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ShellCommandService $shellCommandService
     */
    private $shellCommandService;

    /**
     * @array
     */
    private $folderStructure;

    /**
     * @var ClockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clockMock;

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\TYPO3\Surf\Domain\Service\ShellCommandService $shellCommandService */
        $this->shellCommandService = $this->createMock(ShellCommandService::class);
        $this->task = $this->createTask();
        if ($this->task instanceof ShellCommandServiceAwareInterface) {
            $this->task->setShellCommandService($this->shellCommandService);
        }

        $this->node = new Node('TestNode');
        $this->node->setHostname('hostname');
        $this->deployment = new Deployment('TestDeployment');
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface $mockLogger */
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
    public function doNothingJustLogDebugIfOptionKeepReleasesIsNotDefined()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface $logger */
        $logger = $this->deployment->getLogger();
        $logger->expects($this->once())->method('debug');
        $this->assertNull($this->task->execute($this->node, $this->application, $this->deployment, []));
    }

    /**
     * @test
     */
    public function removeReleases()
    {
        $folders = array_keys($this->folderStructure);
        $this->shellCommandService->expects($this->at(1))->method('execute')->willReturn(implode(' ', $folders));

        $command = array_reduce(['20171108132211', '20171109193135'], function ($carry, $folder) {
            return $carry . sprintf('rm -rf %1$s/%2$s;rm -f %1$s/%2$sREVISION;', $this->application->getReleasesPath(), $folder);
        }, '');

        $this->shellCommandService->expects($this->once())->method('executeOrSimulate')->with($command);
        $this->task->execute($this->node, $this->application, $this->deployment, ['keepReleases' => 1]);
    }

    /**
     * @test
     * @dataProvider keepReleasesByAgeDataProvider
     *
     * @param int $currentTime
     * @param array $identifiers
     * @param int $stringToTime
     * @param array $expectedFoldersToBeRemoved
     */
    public function removeReleasesByAge($currentTime, array $identifiers, $stringToTime, array $expectedFoldersToBeRemoved)
    {
        $this->clockMock->method('currentTime')->willReturn($currentTime);

        $folderStructure['.'] = '.';
        $timestampMap = [];
        foreach ($identifiers as $time) {
            $timestampForCurrentFolder = strtotime($time, $currentTime);
            $timestampMap[] = $timestampForCurrentFolder;
            $folderStructure[strftime('%Y%m%d%H%M%S', $timestampForCurrentFolder)] = ['index.php'];
        }

        $this->clockMock->method('createTimestampFromFormat')->will($this->onConsecutiveCalls(...$timestampMap));
        $this->clockMock->method('stringToTime')->willReturn(strtotime($stringToTime, $currentTime));

        $folders = array_keys($folderStructure);

        $this->shellCommandService->expects($this->at(1))->method('execute')->willReturn(implode(' ', $folders));

        $command = array_reduce(array_map(function ($expectedFolderToBeRemoved) use ($currentTime) {
            return strftime('%Y%m%d%H%M%S', strtotime($expectedFolderToBeRemoved, $currentTime));
        }, $expectedFoldersToBeRemoved), function ($command, $folder) {
            return $command . sprintf('rm -rf %1$s/%2$s;rm -f %1$s/%2$sREVISION;', $this->application->getReleasesPath(), $folder);
        }, '');

        $this->shellCommandService->expects($this->once())->method('executeOrSimulate')->with($command);

        $this->task->execute($this->node, $this->application, $this->deployment, ['onlyRemoveReleasesOlderThan' => 'foo']);
    }

    /**
     * @return array
     */
    public function keepReleasesByAgeDataProvider()
    {
        return [
            // Remove folders older than 121 seconds
            [
                1535191400,
                $identifiers = [
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
                $identifiers = [
                    '1 second ago',
                    '10 minutes ago',
                    '2 apples and 1 second ago',
                    '3 days ago',
                ],
                '2 days ago',
                ['2 days and 1 second ago', '3 days ago'],
            ],
        ];
    }

    /**
     * @return CleanupReleasesTask
     */
    protected function createTask()
    {
        $this->clockMock = $this->getMockBuilder(ClockInterface::class)->getMock();

        return new CleanupReleasesTask($this->clockMock);
    }
}
