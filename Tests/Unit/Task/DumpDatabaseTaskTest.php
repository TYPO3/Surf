<?php
namespace TYPO3\Surf\Tests\Unit\Task;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

/**
 * Unit test for the DumpDatabaseTaskTest
 */
class DumpDatabaseTaskTest extends BaseTaskTest
{
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
     */
    public function executeProperlyEscapesInputOptions()
    {
        $options = array(
            'sourceHost' => 'localhost',
            'sourceUser' => 'user',
            'sourcePassword' => '(pass)',
            'sourceDatabase' => 'db',
            'targetHost' => 'localhost',
            'targetUser' => 'user',
            'targetPassword' => '(pass)',
            'targetDatabase' => 'db',
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted("'mysqldump' '-h' 'localhost' '-u' 'user' '-p(pass)' 'db' | 'ssh' 'hostname' ''\\''mysql'\\'' '\\''-h'\\'' '\\''localhost'\\'' '\\''-u'\\'' '\\''user'\\'' '\\''-p(pass)'\\'' '\\''db'\\'''");
    }

    /**
     * @return \TYPO3\Surf\Domain\Model\Task
     */
    protected function createTask()
    {
        return new \TYPO3\Surf\Task\DumpDatabaseTask();
    }
}
