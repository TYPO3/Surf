<?php

namespace TYPO3\Surf\Tests\Unit\Task\Package;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Package\GitTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class GitTaskTest extends BaseTaskTest
{
    /**
     * @test
     */
    public function missingRepositoryUrlThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @return GitTask
     */
    protected function createTask()
    {
        return new GitTask();
    }
}
