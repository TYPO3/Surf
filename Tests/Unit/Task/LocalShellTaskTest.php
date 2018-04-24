<?php


namespace TYPO3\Surf\Tests\Unit\Task;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Task\LocalShellTask;

class LocalShellTaskTest extends BaseTaskTest
{

    /**
     * @var LocalShellTask
     */
    protected $task;

    /**
     * Set up test dependencies
     */
    protected function setUp()
    {
        parent::setUp();

        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Missing "command" option for TYPO3\Surf\Task\LocalShellTask
     */
    public function executeThrowInvalidConfigurationException()
    {
        $this->task->execute($this->node, $this->application, $this->deployment, array());
    }

    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function simluateThrowInvalidConfigurationException()
    {
        $this->task->simulate($this->node, $this->application, $this->deployment, array());
    }

    /**
     * @test
     */
    public function executeReplacePaths()
    {
        $options = array(
            'command' => 'command',
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('command');
    }

    /**
     * @test
     */
    public function simluateReplacePaths()
    {
        $options = array(
            'command' => 'command',
        );
        $this->task->simulate($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('command');
    }

    /**
     * @test
     */
    public function simulateReplacePathsWithIgnoreErrorsAndLogOutput()
    {
        $options = array(
            'command'      => 'command',
            'ignoreErrors' => true,
            'logOutput'    => true,
        );
        $this->task->simulate($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('command');
    }

    /**
     * @test
     */
    public function executeReplacePathsWithIgnoreErrorsAndLogOutput()
    {
        $options = array(
            'command'      => 'command',
            'ignoreErrors' => true,
            'logOutput'    => true,
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('command');
    }

    /**
     * @test
     */
    public function rollbackWithoutCommandReturnsNull()
    {
        $this->assertNull($this->task->rollback($this->node, $this->application, $this->deployment, array()));
    }

    /**
     * @test
     */
    public function rollbackWithCommandReplacePaths()
    {
        $options = array(
            'rollbackCommand' => 'command',
        );
        $this->task->rollback($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('command');
    }

    /**
     * @return LocalShellTask
     */
    protected function createTask()
    {
        /** @var $replacePathServiceMock \TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
        $replacePathServiceMock = $this->getMockBuilder('TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface')->getMock();
        $replacePathServiceMock->expects($this->any())->method('replacePaths')->willReturn('command');
        return new LocalShellTask($replacePathServiceMock);
    }


}
