<?php
namespace TYPO3\Surf\Tests\Unit\Task\Generic;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Task\Generic\CreateDirectoriesTask;
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
    public function createsDirectoriesInReleasePath()
    {
        $options = array('directories' => array('media'));
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted("cd {$this->deployment->getApplicationReleasePath($this->application)}");
        $this->assertCommandExecuted('mkdir -p media');
    }

    /**
     * @test
     */
    public function createsDirectoriesInCustomPath()
    {
        $options = array('directories' => array('media'), 'baseDirectory' => '/foo/bar');
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('cd /foo/bar');
        $this->assertCommandExecuted('mkdir -p media');
    }
}
