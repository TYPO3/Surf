<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task;

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;
use TYPO3\Surf\Task\GitCheckoutTask;

class GitCheckoutTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): GitCheckoutTask
    {
        return new GitCheckoutTask();
    }

    /**
     * @test
     */
    public function executeWithOutRepositoryUrlThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function executeWithEmptyOptionsAndValidSha1FetchesResetsCopiesAndCleansRepository(): void
    {
        $options = [
            'repositoryUrl' => 'ssh://git.example.com/project/path.git'
        ];
        $this->responses = [
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/main | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('git fetch -q origin');
        $this->assertCommandExecuted('git reset -q --hard d5b7769852a5faa69574fcd3db0799f4ffbd9eec');
        $this->assertCommandExecuted('cp -RPp /home/jdoe/app/cache/transfer/. /home/jdoe/app/releases/');
        $this->assertCommandExecuted('git clean -q -d -x -ff');
    }

    /**
     * @test
     */
    public function executeWithTagOptionAndValidSha1FetchesResetsAndCopiesRepository(): void
    {
        $options = [
            'repositoryUrl' => 'ssh://git.example.com/project/path.git',
            'tag' => 'myTag'
        ];
        $this->responses = [
            'git ls-remote --sort=version:refname ssh://git.example.com/project/path.git \'refs/tags/myTag\' | awk \'{print $1 }\' | tail --lines=1' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('git fetch -q origin');
        $this->assertCommandExecuted('git reset -q --hard d5b7769852a5faa69574fcd3db0799f4ffbd9eec');
        $this->assertCommandExecuted('cp -RPp /home/jdoe/app/cache/transfer/. /home/jdoe/app/releases/');
    }

    /**
     * @test
     */
    public function executeWithTagWildcardOptionAndValidSha1FetchesResetsAndCopiesRepository(): void
    {
        $options = [
            'repositoryUrl' => 'ssh://git.example.com/project/path.git',
            'tag' => 'staging-*'
        ];
        $this->responses = [
            'git ls-remote --sort=version:refname ssh://git.example.com/project/path.git \'refs/tags/staging-*\' | awk \'{print $1 }\' | tail --lines=1' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('git fetch -q origin');
        $this->assertCommandExecuted('git reset -q --hard d5b7769852a5faa69574fcd3db0799f4ffbd9eec');
        $this->assertCommandExecuted('cp -RPp /home/jdoe/app/cache/transfer/. /home/jdoe/app/releases/');
    }

    /**
     * @test
     */
    public function executeWithBranchOptionAndValidSha1FetchesResetsAndCopiesRepository(): void
    {
        $options = [
            'repositoryUrl' => 'ssh://git.example.com/project/path.git',
            'branch' => 'release/production'
        ];
        $this->responses = [
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/release/production | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('git fetch -q origin');
        $this->assertCommandExecuted('git reset -q --hard d5b7769852a5faa69574fcd3db0799f4ffbd9eec');
        $this->assertCommandExecuted('cp -RPp /home/jdoe/app/cache/transfer/. /home/jdoe/app/releases/');
    }

    /**
     * @test
     */
    public function executeWithDisabledRecursiveSubmodulesOptionDoesNotUpdateSubmodulesRecursively(): void
    {
        $options = [
            'repositoryUrl' => 'ssh://git.example.com/project/path.git',
            'recursiveSubmodules' => false
        ];
        $this->responses = [
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/main | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('/git submodule -q update --init (?!--recursive)/');
    }

    /**
     * @test
     */
    public function executeWithoutRecursiveSubmodulesOptionUpdatesSubmodulesRecursively(): void
    {
        $options = [
            'repositoryUrl' => 'ssh://git.example.com/project/path.git'
        ];
        $this->responses = [
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/main | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('/git submodule -q update --init --recursive/');
    }

    /**
     * @test
     */
    public function executeWithFetchAllTagsOptionExecutesFetchTags(): void
    {
        $options = [
            'repositoryUrl' => 'ssh://git.example.com/project/path.git',
            'fetchAllTags' => true
        ];
        $this->responses = [
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/main | awk \'{print $1 }\'' => 'd5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('git fetch --tags');
    }

    /**
     * @test
     */
    public function executeWithEmptyOptionsAndInvalidSha1ThrowsException(): void
    {
        $this->expectException(TaskExecutionException::class);

        $options = [
            'repositoryUrl' => 'ssh://git.example.com/project/path.git'
        ];
        $this->responses = [
            'git ls-remote ssh://git.example.com/project/path.git refs/heads/main | awk \'{print $1 }\'' => 'foo-bar d5b7769852a5faa69574fcd3db0799f4ffbd9eec'
        ];

        try {
            $this->task->execute($this->node, $this->application, $this->deployment, $options);
        } catch (TaskExecutionException $exception) {
            self::assertSame(1335974926, $exception->getCode());
            throw $exception;
        }
    }
}
