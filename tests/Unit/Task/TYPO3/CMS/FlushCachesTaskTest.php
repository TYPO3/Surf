<?php

namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Prophecy\Argument;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class FlushCachesTaskTest extends BaseTaskTest
{
    /**
     * @test
     */
    public function wrongApplicationTypeGivenThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function noSuitableCliArgumentsGiven(): void
    {
        $application = new CMS();
        $this->task->execute($this->node, $application, $this->deployment, []);
        $this->mockLogger->warning(Argument::any())->shouldBeCalledOnce();
    }

    /**
     * @test
     */
    public function executeFlushCacheCommandSuccessfully(): void
    {
        $application = new CMS();
        $options = ['scriptFileName' => 'typo3cms'];
        $this->task->execute($this->node, $application, $this->deployment, $options);
        $this->assertCommandExecuted('/php \'typo3cms\' \'cache:flush\'$/');
    }

    protected function createTask()
    {
        return new FlushCachesTask();
    }
}
