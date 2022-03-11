<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Neos\Flow;

use InvalidArgumentException;
use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Task\Neos\Flow\PublishResourcesTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class PublishResourcesTaskTest extends BaseTaskTest
{
    protected function createTask(): PublishResourcesTask
    {
        return new PublishResourcesTask();
    }

    /**
     * @test
     */
    public function noFlowApplicationGivenThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     */
    public function tooLowFlowVersionDoesNotRunTheTask(): void
    {
        $this->application = new Flow();
        $this->application->setVersion('2.9');
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     */
    public function version3RunsTheTask(): void
    {
        $this->application = new Flow();
        $this->application->setVersion('3.0');
        $this->task->execute($this->node, $this->application, $this->deployment);

        $this->assertCommandExecuted("cd /releases/{$this->deployment->getReleaseIdentifier()} && FLOW_CONTEXT=Production php ./flow typo3.flow:resource:publish");
    }
}
