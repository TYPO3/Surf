<?php


namespace TYPO3\Surf\Tests\Unit\Task;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Task\ShellTask;


class ShellTaskTest extends BaseTaskTest
{

    /**
     * @var ShellTask
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
     */
    public function executeThrowInvalidConfigurationException()
    {
        $this->task->execute($this->node, $this->application, $this->deployment, array());
    }

    /**
     * @test
     * @dataProvider pathReplacementProvider
     */
    public function executeReplacePaths($search, $replace)
    {
        $options = array(
            'command' => 'ln -s {'.$search.'} softlink',
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/ln -s '.escapeshellarg($replace).' softlink/');
    }

    /**
     * @test
     * @dataProvider pathReplacementProvider
     */
    public function simluateReplacePaths($search, $replace)
    {
        $options = array(
            'command' => 'ln -s {'.$search.'} softlink',
        );
        $this->task->simulate($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/ln -s '.escapeshellarg($replace).' softlink/');
    }

    /**
     * @test
     * @dataProvider pathReplacementProvider
     */
    public function executeReplacePathsWithIgnoreErrorsAndLogOutput($search, $replace)
    {
        $options = array(
            'command' => 'ln -s {'.$search.'} softlink',
            'ignoreErrors' => true,
            'logOutput' => true,
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/ln -s '.escapeshellarg($replace).' softlink/');
    }

    /**
     * @test
     * @dataProvider pathReplacementProvider
     */
    public function simluateReplacePathsWithIgnoreErrorsAndLogOutput($search, $replace)
    {
        $options = array(
            'command' => 'ln -s {'.$search.'} softlink',
            'ignoreErrors' => true,
            'logOutput' => true,
        );
        $this->task->simulate($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/ln -s '.escapeshellarg($replace).' softlink/');
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
     * @dataProvider pathReplacementProvider
     */
    public function rollbackWithCommandReplacePaths($search, $replace)
    {
        $options = array(
            'rollbackCommand' => 'ln -s {'.$search.'} softlink',
        );
        $this->task->rollback($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/ln -s '.escapeshellarg($replace).' softlink/');
    }

    /**
     * @return array
     */
    public function pathReplacementProvider()
    {
        return array(
            array('deploymentPath', '\/home\/jdoe\/app'),
            array('sharedPath', '\/home\/jdoe\/app\/shared'),
            array('releasePath', '\/home\/jdoe\/app\/releases\/[0-9]+'),
            array('currentPath', '\/home\/jdoe\/app\/releases\/current'),
            array('previousPath', '\/home\/jdoe\/app\/releases\/previous'),
        );
    }

    /**
     * @return ShellTask
     */
    protected function createTask()
    {
        return new ShellTask();
    }


}
