<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task;

use TYPO3\Surf\Task\RsyncFoldersTask;

class RsyncFoldersTaskTest extends BaseTaskTest
{
    protected function createTask(): RsyncFoldersTask
    {
        return new RsyncFoldersTask();
    }

    /**
     * @test
     */
    public function emptyFoldersOptionsReturnsVoid(): void
    {
        self::assertNull($this->task->execute($this->node, $this->application, $this->deployment));
    }

    /**
     * @test
     * @dataProvider executeWithDifferentOptions
     *
     * @param array<int, string>|string $expectedCommands
     * @param array<string, mixed> $options
     */
    public function executeSuccessfully($expectedCommands, array $options): void
    {
        if (isset($options['port'])) {
            $this->node->setPort($options['port']);
        }

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        foreach ((array)$expectedCommands as $expectedCommand) {
            $this->assertCommandExecuted($expectedCommand);
        }
    }

    public function executeWithDifferentOptions(): \Iterator
    {
        yield [
            'rsync -avz --delete -e ssh uploads/spaceship/ hostname:/var/www/outerspace/uploads/spaceship/',
            [
                'folders' => [
                    ['uploads/spaceship', '/var/www/outerspace/uploads/spaceship']
                ]
            ]
        ];
        yield [
            'rsync -avz --delete -e ssh uploads/spaceship/ username@hostname:/var/www/outerspace/uploads/spaceship/',
            [
                'username' => 'username',
                'folders' => [
                    ['uploads/spaceship', '/var/www/outerspace/uploads/spaceship']
                ]
            ]
        ];
        yield [
            [
                'rsync -avz --delete -e ssh uploads/spaceship1/ username@hostname:/var/www/outerspace/uploads/spaceship1/',
                'rsync -avz --delete -e ssh uploads/spaceship2/ username@hostname:/var/www/outerspace/uploads/spaceship2/',
                'rsync -avz --delete -e ssh uploads/spaceship3/ username@hostname:/var/www/outerspace/uploads/spaceship3/',
            ],
            [
                'username' => 'username',
                'folders' => [
                    ['uploads/spaceship1', '/var/www/outerspace/uploads/spaceship1'],
                    ['uploads/spaceship2', '/var/www/outerspace/uploads/spaceship2'],
                    'uploads/spaceship3' => '/var/www/outerspace/uploads/spaceship3'
                ]
            ]
        ];
        yield [
            'rsync -avz --delete -e ssh -P \'222\' uploads/spaceship/ username@hostname:/var/www/outerspace/uploads/spaceship/',
            [
                'username' => 'username',
                'port' => 222,
                'folders' => [
                    ['uploads/spaceship', '/var/www/outerspace/uploads/spaceship']
                ]
            ]
        ];
    }
}
