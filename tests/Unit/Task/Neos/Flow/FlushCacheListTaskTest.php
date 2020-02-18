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
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Neos\Flow\FlushCacheListTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class FlushCacheListTaskTest extends BaseTaskTest
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
    public function requiredOptionFlushCacheListNotGivenThrowsException()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->application = new Flow();
        $this->task->execute($this->node, $this->application, $this->deployment, ['flushCacheList' => '']);
    }

    /**
     * @test
     */
    public function tooLowFlowVersionThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->application = new Flow();
        $this->application->setVersion('1.0');
        $this->task->execute($this->node, $this->application, $this->deployment, ['flushCacheList' => 'list']);
    }

    /**
     * @test
     */
    public function executeSuccessfully()
    {
        $this->application = new Flow();
        $this->task->execute($this->node, $this->application, $this->deployment, ['flushCacheList' => 'list']);
        $this->assertCommandExecuted(sprintf('cd /releases/%s && FLOW_CONTEXT=Production php ./flow neos.flow:cache:flushone \'--identifier\' \'list\'', $this->deployment->getReleaseIdentifier()));
    }

    /**
     * @return FlushCacheListTask
     */
    protected function createTask()
    {
        return new FlushCacheListTask();
    }
}
