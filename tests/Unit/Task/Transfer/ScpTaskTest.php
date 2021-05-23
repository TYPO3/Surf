<?php

namespace TYPO3\Surf\Tests\Unit\Task\Transfer;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Task\Transfer\ScpTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class ScpTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new Flow('TestApplication');

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @return ScpTask
     */
    protected function createTask()
    {
        return new ScpTask();
    }

    /**
     * @test
     */
    public function executeWithoutExcludes(): void
    {
        $this->node->setOption('hostname', 'myserver.local');
        $this->node->setOption('username', 'jdoe');

        $releaseIdentifier = $this->deployment->getReleaseIdentifier();

        $expectedCommands = [
            'mkdir -p /home/jdoe/app/cache/transfer',
            'rm -rf ./Data/Surf/TestDeployment/TestApplication/*.tar.gz',
            sprintf(
                'cd ./Data/Surf/TestDeployment/TestApplication/; tar --exclude=".git" --exclude="%1$s.tar.gz" -czf %1$s.tar.gz -C ./Data/Surf/TestDeployment/TestApplication .',
                $releaseIdentifier
            ),
            sprintf(
                'scp ./Data/Surf/TestDeployment/TestApplication/%s.tar.gz jdoe@myserver.local:/home/jdoe/app/cache/transfer',
                $releaseIdentifier
            ),
            sprintf(
                'tar -xzf /home/jdoe/app/cache/transfer/%1$s.tar.gz -C /home/jdoe/app/releases/%1$s',
                $releaseIdentifier
            ),
            sprintf('rm -f /home/jdoe/app/cache/transfer/%s.tar.gz', $releaseIdentifier),
            sprintf('rm -f ./Data/Surf/TestDeployment/TestApplication/%s.tar.gz', $releaseIdentifier),
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, []);

        foreach ($expectedCommands as $expectedCommand) {
            $this->assertCommandExecuted($expectedCommand);
        }
    }

    /**
     * @test
     */
    public function executeWithAdditionalExcludes(): void
    {
        $this->node->setOption('hostname', 'myserver.local');
        $this->node->setOption('username', 'jdoe');

        $releaseIdentifier = $this->deployment->getReleaseIdentifier();

        $this->task->execute($this->node, $this->application, $this->deployment, ['scpExcludes' => ['file.txt']]);

        $this->assertCommandExecuted(
            sprintf(
                'cd ./Data/Surf/TestDeployment/TestApplication/; tar --exclude=".git" --exclude="%1$s.tar.gz" --exclude="file.txt" -czf %1$s.tar.gz -C ./Data/Surf/TestDeployment/TestApplication .',
                $releaseIdentifier
            )
        );
    }
}
