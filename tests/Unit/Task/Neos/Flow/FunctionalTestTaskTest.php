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
use TYPO3\Surf\Task\Neos\Flow\FunctionalTestTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class FunctionalTestTaskTest extends BaseTaskTest
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
    public function executeSuccessfully()
    {
        $this->application = new Flow();
        $this->task->execute($this->node, $this->application, $this->deployment, ['flushCacheList' => 'list']);
        $this->assertCommandExecuted(sprintf('cd /releases/%s && phpunit -c Build/BuildEssentials/PhpUnit/FunctionalTests.xml', $this->deployment->getReleaseIdentifier()));
    }

    /**
     * @return FunctionalTestTask
     */
    protected function createTask()
    {
        return new FunctionalTestTask();
    }
}
