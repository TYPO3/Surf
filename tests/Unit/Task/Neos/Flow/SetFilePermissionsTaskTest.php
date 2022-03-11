<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Neos\Flow;

use InvalidArgumentException;
use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Task\Neos\Flow\SetFilePermissionsTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class SetFilePermissionsTaskTest extends BaseTaskTest
{
    protected function createTask(): SetFilePermissionsTask
    {
        return new SetFilePermissionsTask();
    }

    /**
     * @test
     */
    public function noFlowApplicationGivenThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     * @dataProvider executeWithDifferentOptions
     *
     * @param string $expectedCommand
     * @param array $options
     */
    public function executeSuccessfully($expectedCommand, array $options = []): void
    {
        $this->application = new Flow();
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(sprintf($expectedCommand, $this->deployment->getReleaseIdentifier()));
    }

    public function executeWithDifferentOptions(): array
    {
        return [
            [
                // Default
                'cd /releases/%s && FLOW_CONTEXT=Production php ./flow neos.flow:core:setfilepermissions \'root\' \'www-data\' \'www-data\'',
            ],
            [
                // Override the shellUsername and the username. The shellUsername has the precedence
                'cd /releases/%s && FLOW_CONTEXT=Production php ./flow neos.flow:core:setfilepermissions \'shellUsername\' \'www-data\' \'www-data\'',
                ['shellUsername' => 'shellUsername', 'username' => 'no_effect']
            ],
            [
                'cd /releases/%s && FLOW_CONTEXT=Production php ./flow neos.flow:core:setfilepermissions \'username\' \'www-data\' \'www-data\'',
                ['username' => 'username']
            ],
            [
                'cd /releases/%s && FLOW_CONTEXT=Production php ./flow neos.flow:core:setfilepermissions \'root\' \'webserverUsername\' \'www-data\'',
                ['webserverUsername' => 'webserverUsername']
            ],
            [
                'cd /releases/%s && FLOW_CONTEXT=Production php ./flow neos.flow:core:setfilepermissions \'root\' \'webserverUsername\' \'webserverGroupname\'',
                ['webserverUsername' => 'webserverUsername', 'webserverGroupname' => 'webserverGroupname']
            ],
        ];
    }
}
