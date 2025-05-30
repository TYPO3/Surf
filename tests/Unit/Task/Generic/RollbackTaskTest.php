<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Generic;

use TYPO3\Surf\Task\Generic\RollbackTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

final class RollbackTaskTest extends BaseTaskTest
{
    protected function createTask(): RollbackTask
    {
        return new RollbackTask();
    }

    /**
     * @test
     */
    public function executeSuccessfully(): void
    {
        $releasesPath = $this->node->getReleasesPath();
        $previousReleasePath = $releasesPath . '/previous';
        $currentReleasePath = $releasesPath . '/current';

        $this->responses = [
            sprintf('if [ -d %1$s/. ]; then find %1$s/. -maxdepth 1 -type d -exec basename {} \; ; fi', $releasesPath) => '.
20180430105845
20180430130050
20180430154032
current
previous',
            sprintf('if [ -h %1$s ]; then basename `readlink %1$s` ; fi', $previousReleasePath) => '20180430130050',
            sprintf('if [ -h %1$s ]; then basename `readlink %1$s` ; fi', $currentReleasePath) => '20180430154032',
        ];
        $this->task->execute($this->node, $this->application, $this->deployment);

        $symlinkCommand = sprintf('cd %1$s && ln -sfn ./%2$s current', $releasesPath, '20180430130050');
        $removeCommand = sprintf('rm -rf %1$s/%2$s; rm -rf %1$s/%2$sREVISION;', $releasesPath, '20180430154032');
        $this->assertCommandExecuted($symlinkCommand);
        $this->assertCommandExecuted($removeCommand);
    }

    /**
     * @test
     */
    public function canNotRollbackTooFewReleasesExist(): void
    {
        $releasesPath = $this->node->getReleasesPath();

        $this->responses = [
            sprintf('if [ -d %1$s/. ]; then find %1$s/. -maxdepth 1 -type d -exec basename {} \; ; fi', $releasesPath) => '.
20180430130050
current
previous',
        ];
        $this->mockLogger->expects(self::once())->method('notice')->with('No more releases you can revert to.');

        $this->task->execute($this->node, $this->application, $this->deployment);

        self::assertCount(1, $this->commands['executed']);
    }
}
