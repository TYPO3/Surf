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
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Neos\Flow\RunCommandTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class RunCommandTaskTest extends BaseTaskTest
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
     */
    public function requiredOptionCommandNotGivenThrowsException()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->application = new Flow();
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
                'cd /releases/%s && FLOW_CONTEXT=Production ./flow command \'argument1\' \'argument2\'',
                ['command' => 'command', 'arguments' => ['argument1', 'argument2']]
            ],
            [
                'cd /releases/%s && FLOW_CONTEXT=Production ./flow command \'argument1\'',
                ['command' => 'command', 'arguments' => 'argument1']
            ],
            [
                'cd /releases/%s && FLOW_CONTEXT=Production ./flow command',
                ['command' => 'command']
            ],
        ];
    }

    /**
     * @return RunCommandTask
     */
    protected function createTask()
    {
        return new RunCommandTask();
    }
}
