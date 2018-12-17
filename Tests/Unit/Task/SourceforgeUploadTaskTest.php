<?php

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\SourceforgeUploadTask;

class SourceforgeUploadTaskTest extends BaseTaskTest
{

    /**
     * @var SourceforgeUploadTask
     */
    protected $task;

    /**
     * @test
     */
    public function missingRequiredOptionThrowsException()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function executeSuccessfully()
    {
        $options = [
            'sourceforgeProjectName' => 'sourceforgeProjectName',
            'sourceforgePackageName' => 'sourceforgePackageName',
            'sourceforgeUserName' => 'sourceforgeUserName',
            'version' => '1.0',
            'files' => [
                'file1.php',
                'file2.php',
            ],
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/rsync -e ssh file1.php file2.php 'sourceforgeUserName,sourceforgeProjectName@frs.sourceforge.net:\/home\/frs\/project\/s\/so\/sourceforgeProjectName\/sourceforgePackageName\/1.0'/");
    }

    /**
     * @return SourceforgeUploadTask
     */
    protected function createTask()
    {
        return new SourceforgeUploadTask();
    }
}
