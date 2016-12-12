<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Task\TYPO3\Flow\CreateDirectoriesTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Class CreateDirectoriesTaskTest
 */
class CreateDirectoriesTaskTest extends BaseTaskTest
{

    /**
     * @var CreateDirectoriesTask
     */
    protected $task;

    protected function setUp()
    {
        parent::setUp();
        $this->application = new \TYPO3\Surf\Application\TYPO3\CMS('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @return CreateDirectoriesTask
     */
    protected function createTask()
    {
        return new CreateDirectoriesTask();
    }

    /**
     * @test
     */
    public function createsDirectoriesInDeploymentRoot()
    {
        $options = array();
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted("cd {$this->application->getDeploymentPath()}");
        $this->assertCommandExecuted('mkdir -p shared/Data/Logs');
        $this->assertCommandExecuted('mkdir -p shared/Data/Persistent');
        $this->assertCommandExecuted('mkdir -p shared/Configuration');
    }
}
