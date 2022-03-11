<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Composer;

use TYPO3\Surf\Task\Composer\DownloadTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class DownloadTaskTest extends BaseTaskTest
{
    protected function createTask(): DownloadTask
    {
        return new DownloadTask();
    }

    /**
     * @test
     */
    public function executeWithDefaultComposerDownloadCommand(): void
    {
        $applicationReleasePath = $this->deployment->getApplicationReleasePath($this->application);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
        $this->assertCommandExecuted(sprintf('cd %s && %s', escapeshellarg($applicationReleasePath), 'curl -s https://getcomposer.org/installer | php'));
    }

    /**
     * @test
     */
    public function executeWithCustomComposerDownloadCommand(): void
    {
        $applicationReleasePath = $this->deployment->getApplicationReleasePath($this->application);
        $options = ['composerDownloadCommand' => 'curl -s https://custom.domain.org/installer | php'];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(sprintf('cd %s && %s', escapeshellarg($applicationReleasePath), 'curl -s https://custom.domain.org/installer | php'));
    }
}
