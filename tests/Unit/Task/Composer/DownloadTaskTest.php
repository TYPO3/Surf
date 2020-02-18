<?php

namespace TYPO3\Surf\Tests\Unit\Task\Composer;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Task\Composer\DownloadTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class DownloadTaskTest extends BaseTaskTest
{

    /**
     * @test
     */
    public function executeWithDefaultComposerDownloadCommand()
    {
        $applicationReleasePath = $this->deployment->getApplicationReleasePath($this->application);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
        $this->assertCommandExecuted(sprintf('cd %s && %s', escapeshellarg($applicationReleasePath), 'curl -s https://getcomposer.org/installer | php'));
    }

    /**
     * @test
     */
    public function executeWithCustomComposerDownloadCommand()
    {
        $applicationReleasePath = $this->deployment->getApplicationReleasePath($this->application);
        $options = ['composerDownloadCommand' => 'curl -s https://custom.domain.org/installer | php'];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(sprintf('cd %s && %s', escapeshellarg($applicationReleasePath), 'curl -s https://custom.domain.org/installer | php'));
    }

    protected function createTask()
    {
        return new DownloadTask();
    }
}
