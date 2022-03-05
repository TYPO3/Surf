<?php

declare(strict_types=1);

namespace TYPO3\Surf\Tests\Unit\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Task\Neos\Flow\WarmUpCacheTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class WarmUpCacheTaskTest extends BaseTaskTest
{
    /**
     * @test
     */
    public function executeSuccessfully(): void
    {
        $this->application = new Flow();
        $this->task->execute($this->node, $this->application, $this->deployment, []);
        $this->assertCommandExecuted(
            sprintf(
                'cd /releases/%s && FLOW_CONTEXT=Production php ./flow neos.flow:cache:warmup',
                $this->deployment->getReleaseIdentifier()
            )
        );
    }

    /**
     * @return WarmUpCacheTask
     */
    protected function createTask(): WarmUpCacheTask
    {
        return new WarmUpCacheTask();
    }
}
