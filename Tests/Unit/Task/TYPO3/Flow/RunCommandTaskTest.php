<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Task\Neos\Flow\RunCommandTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the RunCommandTask
 */
class RunCommandTaskTest extends BaseTaskTest
{
    /**
     * Set up test dependencies
     */
    protected function setUp()
    {
        parent::setUp();

        $this->application = new Flow('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeWithSingleStringArgumentsEscapesFullArgument()
    {
        $options = [
            'command' => 'example:command',
            'arguments' => 'Some longer argument needing "escaping"',
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('./flow example:command \'Some longer argument needing "escaping"\'');
    }

    /**
     * @test
     */
    public function executeWithArrayArgumentsEscapesIndividualArguments()
    {
        $options = [
            'command' => 'site:prune',
            'arguments' => ['--confirmation', 'TRUE'],
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('./flow site:prune \'--confirmation\' \'TRUE\'');
    }

    /**
     * @return \TYPO3\Surf\Domain\Model\Task
     */
    protected function createTask()
    {
        return new RunCommandTask();
    }
}
//'cd /home/jdoe/app/releases/20190201165619 && FLOW_CONTEXT=Production  ./flow neos.flow:example:command 'Some longer argument needing "escaping"'
