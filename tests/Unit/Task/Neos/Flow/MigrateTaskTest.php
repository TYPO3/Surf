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
use TYPO3\Surf\Task\Neos\Flow\MigrateTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class MigrateTaskTest extends BaseTaskTest
{
    protected function createTask(): MigrateTask
    {
        return new MigrateTask();
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
    public function executeSuccessfully(): void
    {
        $this->application = new Flow();
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted(
            sprintf(
                'cd /releases/%s && FLOW_CONTEXT=Production php ./flow neos.flow:doctrine:migrate',
                $this->deployment->getReleaseIdentifier()
            )
        );
    }
}
