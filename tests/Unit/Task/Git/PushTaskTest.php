<?php

namespace TYPO3\Surf\Tests\Unit\Task\Git;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Git\PushTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class PushTaskTest extends BaseTaskTest
{
    /**
     * @test
     */
    public function missingRemoteOptionThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function missingRefSpecOptionThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['remote' => 'https://github.com/12345/12345']
        );
    }

    /**
     * @test
     */
    public function executeGitPushSuccessfully(): void
    {
        $options = ['remote' => 'https://github.com/12345/12345', 'refspec' => 'master:refs/heads/qa/master'];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(
            sprintf(
                'cd ' . $this->deployment->getApplicationReleasePath($this->node) . '; git push -f %s %s',
                $options['remote'],
                $options['refspec']
            )
        );
    }

    /**
     * @test
     */
    public function executeGitPushWithRecurseIntoSubmodulesSuccessfully(): void
    {
        $options = [
            'remote' => 'https://github.com/12345/12345',
            'refspec' => 'master:refs/heads/qa/master',
            'recurseIntoSubmodules' => true
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $targetPath = $this->deployment->getApplicationReleasePath($this->node);
        $this->assertCommandExecuted(
            sprintf('cd ' . $targetPath . '; git push -f %s %s', $options['remote'], $options['refspec'])
        );
        $this->assertCommandExecuted(
            sprintf(
                'cd ' . $targetPath . '; git submodule foreach \'git push -f %s %s\'',
                $options['remote'],
                $options['refspec']
            )
        );
    }

    /**
     * @return PushTask
     */
    protected function createTask()
    {
        return new PushTask();
    }
}
