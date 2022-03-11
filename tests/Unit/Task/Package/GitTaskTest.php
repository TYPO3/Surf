<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Package;

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Package\GitTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class GitTaskTest extends BaseTaskTest
{
    protected function createTask(): GitTask
    {
        return new GitTask();
    }

    /**
     * @test
     */
    public function missingRepositoryUrlThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }
}
