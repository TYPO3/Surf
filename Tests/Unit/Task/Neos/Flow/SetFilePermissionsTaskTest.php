<?php

namespace TYPO3\Surf\Tests\Unit\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use InvalidArgumentException;
use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Task\Neos\Flow\SetFilePermissionsTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class SetFilePermissionsTaskTest extends BaseTaskTest
{

    /**
     * @test
     */
    public function noFlowApplicationGivenThrowsException()
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
    public function executeSuccessfully($expectedCommand, array $options = [])
    {
        $this->application = new Flow();
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(sprintf($expectedCommand, $this->deployment->getReleaseIdentifier()));
    }

    /**
     * @return array
     */
    public function executeWithDifferentOptions()
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

    /**
     * @return SetFilePermissionsTask
     */
    protected function createTask()
    {
        return new SetFilePermissionsTask();
    }
}
