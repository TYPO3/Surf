<?php
namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

/**
 * Unit test for the GitCheckoutTask
 */
class GitCheckoutTaskTest extends BaseTaskTest
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
    public function executeWithEmptyOptionsAndValidSha1FetchesResetsCopiesAndCleansRepository()
    {
        $options = array(
            'repositoryUrl' => 'ssh://git.example.com/project/path.git'
        );
        $this->responses = array(
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/master | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('git fetch -q origin');
        $this->assertCommandExecuted('git reset -q --hard d5b7769852a5faa69574fcd3db0799f4ffbd9eec');
        $this->assertCommandExecuted('cp -RPp /home/jdoe/app/cache/transfer/. /home/jdoe/app/releases/');
        $this->assertCommandExecuted('git clean -q -d -x -ff');
    }

    /**
     * @test
     */
    public function executeWithBranchOptionAndValidSha1FetchesResetsAndCopiesRepository()
    {
        $options = array(
            'repositoryUrl' => 'ssh://git.example.com/project/path.git',
            'branch' => 'release/production'
        );
        $this->responses = array(
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/release/production | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('git fetch -q origin');
        $this->assertCommandExecuted('git reset -q --hard d5b7769852a5faa69574fcd3db0799f4ffbd9eec');
        $this->assertCommandExecuted('cp -RPp /home/jdoe/app/cache/transfer/. /home/jdoe/app/releases/');
    }

    /**
     * @test
     */
    public function executeWithDisabledRecursiveSubmodulesOptionDoesNotUpdateSubmodulesRecursively()
    {
        $options = array(
            'repositoryUrl' => 'ssh://git.example.com/project/path.git',
            'recursiveSubmodules' => false
        );
        $this->responses = array(
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/master | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('/git submodule -q update --init (?!--recursive)/');
    }

    /**
     * @test
     */
    public function executeWithoutRecursiveSubmodulesOptionUpdatesSubmodulesRecursively()
    {
        $options = array(
            'repositoryUrl' => 'ssh://git.example.com/project/path.git'
        );
        $this->responses = array(
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/master | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('/git submodule -q update --init --recursive/');
    }

    /**
     * @test
     */
    public function executeWithFetachAllTagsOptionExecutesFetchTags()
    {
        $options = array(
            'repositoryUrl' => 'ssh://git.example.com/project/path.git',
            'fetchAllTags' => true
        );
        $this->responses = array(
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/master | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('git fetch --tags');
    }

    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception\TaskExecutionException
     */
    public function executeWithEmptyOptionsAndInvalidSha1ThrowsException()
    {
        $options = array(
            'repositoryUrl' => 'ssh://git.example.com/project/path.git'
        );
        $this->responses = array(
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/master | awk \'{print $1 }\'' => 'foo-bar d5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        );

        try {
            $this->task->execute($this->node, $this->application, $this->deployment, $options);
        } catch (\TYPO3\Surf\Exception\TaskExecutionException $exception) {
            $this->assertEquals(1335974926, $exception->getCode());
            throw $exception;
        }
    }

    /**
     * @return \TYPO3\Surf\Domain\Model\Task
     */
    protected function createTask()
    {
        return new \TYPO3\Surf\Task\GitCheckoutTask();
    }
}
