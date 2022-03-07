<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task;

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\DumpDatabaseTask;

/**
 * Unit test for the DumpDatabaseTaskTest
 */
class DumpDatabaseTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function missingOptionThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function executeProperlyEscapesInputOptions(): void
    {
        $options = [
            'sourceHost' => 'localhost',
            'sourceUser' => 'user',
            'sourcePassword' => '(pass)',
            'sourceDatabase' => 'db',
            'targetHost' => 'localhost',
            'targetUser' => 'user',
            'targetPassword' => '(pass)',
            'targetDatabase' => 'db',
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted(
            "'mysqldump' '-h' 'localhost' '-u' 'user' '-p(pass)' 'db' | 'ssh' 'hostname' ''\\''mysql'\\'' '\\''-h'\\'' '\\''localhost'\\'' '\\''-u'\\'' '\\''user'\\'' '\\''-p(pass)'\\'' '\\''db'\\'''"
        );
    }

    /**
     * @return DumpDatabaseTask
     */
    protected function createTask(): DumpDatabaseTask
    {
        return new DumpDatabaseTask();
    }
}
