<?php


namespace TYPO3\Surf\Tests\Unit\Task;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Task\RsyncFoldersTask;


class RsyncFoldersTaskTest extends BaseTaskTest
{

    /**
     * @var RsyncFoldersTask
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
     */
    public function executeRetunrsNullIfOptionFoldersIsNotSet()
    {
        $this->assertNull($this->task->execute($this->node, $this->application, $this->deployment));
    }

    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function executeWithOnlyOneFolderThrowException()
    {
        $options = array(
            'folders' => 'folder',
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @test
     */
    public function executeWithOneFolderPair()
    {
        $options = array(
            'folders' => array(
                array('local', 'remote'),
            ),
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('rsync -avz --delete -e ssh local/ hostname:remote/');
    }

    /**
     * @test
     */
    public function executeWithOneFolderPairAndUsername()
    {
        $options = array(
            'folders'  => array(
                array('local', 'remote'),
            ),
            'username' => 'username',
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('rsync -avz --delete -e ssh local/ username@hostname:remote/');
    }

    /**
     * @test
     */
    public function executeWithOneFolderPairAndUsernameReplacePaths()
    {
        $options = array(
            'folders'  => array(
                array('deploymentPath', '{deploymentPath}'),
                array('sharedPath', '{sharedPath}'),
                array('releasePath', '{releasePath}'),
                array('currentPath', '{currentPath}'),
                array('previousPath', '{previousPath}'),
            ),
            'username' => 'username',
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('rsync -avz --delete -e ssh deploymentPath/ username@hostname:'.escapeshellarg('/home/jdoe/app'));
        $this->assertCommandExecuted('rsync -avz --delete -e ssh sharedPath/ username@hostname:'.escapeshellarg('/home/jdoe/app/shared'));
        $this->assertCommandExecuted('rsync -avz --delete -e ssh currentPath/ username@hostname:'.escapeshellarg('/home/jdoe/app/releases/current'));
        $this->assertCommandExecuted('rsync -avz --delete -e ssh previousPath/ username@hostname:'.escapeshellarg('/home/jdoe/app/releases/previous'));
    }

    /**
     * @return RsyncFoldersTask
     */
    protected function createTask()
    {
        return new RsyncFoldersTask();
    }


}
