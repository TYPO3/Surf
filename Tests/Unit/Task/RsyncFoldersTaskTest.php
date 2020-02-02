<?php

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Task\RsyncFoldersTask;

class RsyncFoldersTaskTest extends BaseTaskTest
{

    /**
     * @test
     */
    public function emptyFoldersOptionsReturnsVoid()
    {
        $this->assertNull($this->task->execute($this->node, $this->application, $this->deployment));
    }

    /**
     * @test
     *
     * @dataProvider executeWithDifferentOptions
     *
     * @param array|string $expectedCommands
     * @param array $options
     */
    public function executeSuccessfully($expectedCommands, array $options)
    {
        if (isset($options['port'])) {
            $this->node->setPort($options['port']);
        }

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        foreach ((array)$expectedCommands as $expectedCommand) {
            $this->assertCommandExecuted($expectedCommand);
        }
    }

    /**
     * @return array
     */
    public function executeWithDifferentOptions()
    {
        return [
            [
                'rsync -avz --delete -e ssh uploads/spaceship/ hostname:/var/www/outerspace/uploads/spaceship/',
                [
                    'folders' => [
                        ['uploads/spaceship', '/var/www/outerspace/uploads/spaceship']
                    ]
                ]
            ],
            [
                'rsync -avz --delete -e ssh uploads/spaceship/ username@hostname:/var/www/outerspace/uploads/spaceship/',
                [
                    'username' => 'username',
                    'folders' => [
                        ['uploads/spaceship', '/var/www/outerspace/uploads/spaceship']
                    ]
                ]
            ],
            [
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
            ],
            [
                'rsync -avz --delete -e ssh -P \'222\' uploads/spaceship/ username@hostname:/var/www/outerspace/uploads/spaceship/',
                [
                    'username' => 'username',
                    'port' => 222,
                    'folders' => [
                        ['uploads/spaceship', '/var/www/outerspace/uploads/spaceship']
                    ]
                ]
            ],
        ];
    }

    /**
     * @return RsyncFoldersTask
     */
    protected function createTask()
    {
        return new RsyncFoldersTask();
    }
}
