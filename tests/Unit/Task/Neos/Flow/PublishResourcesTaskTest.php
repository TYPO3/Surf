<?php

namespace TYPO3\Surf\Tests\Unit\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use InvalidArgumentException;
use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Task\Neos\Flow\PublishResourcesTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class PublishResourcesTaskTest extends BaseTaskTest
{

    /**
     * @test
     */
    public function noFlowApplicationGivenThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     */
    public function tooLowFlowVersionReturnsNull()
    {
        $this->application = new Flow();
        $this->application->setVersion('2.9');
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @return PublishResourcesTask
     */
    protected function createTask()
    {
        return new PublishResourcesTask();
    }
}
