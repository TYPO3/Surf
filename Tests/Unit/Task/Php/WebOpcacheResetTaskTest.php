<?php


namespace TYPO3\Surf\Unit\Task\Php;


use TYPO3\Surf\Task\Php\WebOpcacheResetTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class WebOpcacheResetTaskTest extends BaseTaskTest
{

    /**
     * @var WebOpcacheResetTask|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $task;

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->task->expects($this->any())->method('executeScript')->willReturn('success');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeInDryRunMode()
    {
        $this->deployment->setDryRun(true);
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertEmpty($this->commands['executed']);
    }


    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function noBaseUrlOptionDefined()
    {
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     */
    public function executeWithOutScriptBasePathAndRandomScriptIdentifier()
    {
        $options = array(
            'baseUrl' => 'http://domain.com/'
        );

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $pathToFile = '\/home\/jdoe\/app\/releases\/[0-9]+\/Web\/'.WebOpcacheResetTask::DEFAULT_SCRIPT_PREFIX. '-[a-zA-Z0-9]{32}.php';
        $this->assertCommandExecuted('/echo ' .escapeshellarg(preg_quote(WebOpcacheResetTask::SCRIPT_CODE)). ' > ' .escapeshellarg($pathToFile).'/');
    }

    /**
     * @test
     */
    public function executeWithScriptBasePathAndRandomScriptIdentifier()
    {
        $options = array(
            'baseUrl' => 'http://domain.com/',
            'scriptBasePath' => 'basepath'
        );

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $pathToFile = 'basepath\/' .WebOpcacheResetTask::DEFAULT_SCRIPT_PREFIX. '-[a-zA-Z0-9]{32}.php';
        $this->assertCommandExecuted('/echo ' .escapeshellarg(preg_quote(WebOpcacheResetTask::SCRIPT_CODE)). ' > '.escapeshellarg($pathToFile).'/');
    }

    /**
     * @test
     */
    public function executeWithOutScriptBasePathAndScriptIdentifier()
    {
        $identifier = 'identifier';
        $options = array(
            'baseUrl' => 'http://domain.com/',
            'scriptIdentifier' => $identifier
        );

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $pathToFile = '\/home\/jdoe\/app\/releases\/[0-9]+\/Web\/'.WebOpcacheResetTask::DEFAULT_SCRIPT_PREFIX. '-'.$identifier.'.php';
        $this->assertCommandExecuted('/echo ' .escapeshellarg(preg_quote(WebOpcacheResetTask::SCRIPT_CODE)). ' > ' .escapeshellarg($pathToFile). '/');
    }

    /**
     * @test
     */
    public function executeWithScriptBasePathAndScriptIdentifier()
    {
        $identifier = 'identifier';
        $options = array(
            'baseUrl' => 'http://domain.com/',
            'scriptIdentifier' => $identifier,
            'scriptBasePath' => 'basepath'
        );

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $pathToFile = 'basepath\/'.WebOpcacheResetTask::DEFAULT_SCRIPT_PREFIX. '-'.$identifier. '.php';
        $this->assertCommandExecuted('/echo ' .escapeshellarg(preg_quote(WebOpcacheResetTask::SCRIPT_CODE)). ' > ' .escapeshellarg($pathToFile). '/');
    }



    /**
     * @return WebOpcacheResetTask
     */
    protected function createTask()
    {
        /** @var WebOpcacheResetTask|\PHPUnit_Framework_MockObject_MockObject $mockTask */
        $mockTask = $this->getMock('TYPO3\\Surf\\Task\\Php\\WebOpcacheResetTask', array('executeScript'));
        return $mockTask;
    }


}
