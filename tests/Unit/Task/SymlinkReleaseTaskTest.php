<?php

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Task\SymlinkReleaseTask;

class SymlinkReleaseTaskTest extends BaseTaskTest
{
    /**
     * @test
     */
    public function executeSuccessfully(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted($this->expectedCommand());
    }

    /**
     * @test
     */
    public function simulateSuccessfully(): void
    {
        $this->task->simulate($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted($this->expectedCommand());
    }

    /**
     * @test
     */
    public function rollbackSuccessfully(): void
    {
        $this->task->rollback($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted(
            'cd ' . $this->node->getReleasesPath() . ' && rm -f ./current && mv ./previous ./current'
        );
    }

    /**
     * @return SymlinkReleaseTask
     */
    protected function createTask()
    {
        return new SymlinkReleaseTask();
    }

    /**
     * @return string
     */
    private function expectedCommand(): string
    {
        return 'cd ' . $this->node->getReleasesPath()
            . ' && rm -rf ./previous && if [ -e ./current ]; then mv ./current ./previous; fi && ln -s ./'
            . $this->deployment->getReleaseIdentifier() . ' ./current && rm -rf ./next';
    }
}
