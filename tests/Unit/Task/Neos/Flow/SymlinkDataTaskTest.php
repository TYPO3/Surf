<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Neos\Flow;

use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Task\Neos\Flow\SymlinkDataTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class SymlinkDataTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Flow('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): SymlinkDataTask
    {
        return new SymlinkDataTask();
    }

    /**
     * @test
     */
    public function executeSuccessfully(): void
    {
        $this->application = new Flow();
        $this->task->execute($this->node, $this->application, $this->deployment);

        $this->assertCommandExecuted(sprintf('mkdir -p /releases/%s/Data', $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted(sprintf('cd /releases/%s', $this->deployment->getReleaseIdentifier()));
        $this->assertCommandExecuted('ln -sf ../../../shared/Data/Logs ./Data/Logs');
        $this->assertCommandExecuted('ln -sf ../../../shared/Data/Persistent ./Data/Persistent');
    }
}
