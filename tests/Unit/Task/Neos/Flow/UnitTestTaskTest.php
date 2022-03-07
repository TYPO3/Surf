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
use TYPO3\Surf\Task\Neos\Flow\UnitTestTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class UnitTestTaskTest extends BaseTaskTest
{
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
                'cd /releases/%s && phpunit -c Build/BuildEssentials/PhpUnit/UnitTests.xml',
                $this->deployment->getReleaseIdentifier()
            )
        );
    }

    /**
     * @return UnitTestTask
     */
    protected function createTask(): UnitTestTask
    {
        return new UnitTestTask();
    }
}
