<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task;

use TYPO3\Surf\Task\SymlinkReleaseTask;

class SymlinkReleaseTaskTest extends BaseTaskTest
{
    protected function createTask(): SymlinkReleaseTask
    {
        return new SymlinkReleaseTask();
    }

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
            'cd ' . $this->application->getReleasesPath() . ' && rm -f ./current && mv ./previous ./current'
        );
    }

    private function expectedCommand(): string
    {
        return 'cd ' . $this->application->getReleasesPath()
            . ' && rm -rf ./previous && if [ -e ./current ]; then mv ./current ./previous; fi && ln -s ./'
            . $this->deployment->getReleaseIdentifier() . ' ./current && rm -rf ./next';
    }
}
