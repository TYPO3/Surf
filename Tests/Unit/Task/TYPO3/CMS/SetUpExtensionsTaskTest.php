<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Class SetUpExtensionsTaskTest
 */
class SetUpExtensionsTaskTest extends BaseTaskTest
{
    /**
     * @var SetUpExtensionsTask
     */
    protected $task;


    /**
     * @return SetUpExtensionsTask
     */
    protected function createTask()
    {
        return new SetUpExtensionsTask();
    }

    public function setUp()
    {
        parent::setUp();
        $this->application = new \TYPO3\Surf\Application\TYPO3\CMS('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeWithoutOptionExecutesSetUpActive()
    {
        $this->task->execute($this->node, $this->application, $this->deployment, array());
        $this->assertCommandExecuted("php './typo3cms' 'extension:setupactive'");
    }

    /**
     * @test
     */
    public function executeWithOptionExecutesSetUpWithOption()
    {
        $options = array(
            'extensionKeys' => array('foo', 'bar')
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("php './typo3cms' 'extension:setup' 'foo,bar'");
    }
}
