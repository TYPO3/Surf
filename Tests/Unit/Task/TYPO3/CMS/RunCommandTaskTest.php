<?php


namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */


use TYPO3\Surf\Task\TYPO3\CMS\RunCommandTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;
use TYPO3\Surf\Application\TYPO3\CMS;

class RunCommandTaskTest extends BaseTaskTest
{

    /**
     * @var RunCommandTask
     */
    protected $task;

    protected function setUp()
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');
    }

    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function exceptionThrownBecauseApplicationIsNotOfTypeCMS()
    {
        $wrongApplication = $this->getMockBuilder('TYPO3\Surf\Application\BaseApplication')->disableOriginalConstructor()->getMock();
        $this->task->execute($this->node, $wrongApplication, $this->deployment);
    }

    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function exceptionThrownBecauseNoCommandOptionDefined()
    {
        $this->task->execute($this->node, $this->application, $this->deployment, array());
    }

    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function exceptionThrownBecauseNoScriptFileNameOptionDefined()
    {
        $this->task->execute($this->node, $this->application, $this->deployment, array('command' => 'command'));
    }

    /**
     * @test
     */
    public function executeWithCommandAndScriptFileName()
    {
        $options = array(
            'scriptFileName' => './typo3cms',
            'command' => 'command:any',
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("php './typo3cms' 'command:any'");
    }

    /**
     * @test
     */
    public function executeWithCommandAndScriptFileNameAndArgument()
    {
        $options = array(
            'scriptFileName' => './typo3cms',
            'command' => 'command:any',
            'arguments' => 'any',
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("php './typo3cms' 'command:any' 'any'");
    }

    /**
     * @return RunCommandTask
     */
    protected function createTask()
    {
        return new RunCommandTask();
    }


}
