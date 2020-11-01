<?php
namespace TYPO3\Surf\Tests\Unit\Task\Transfer;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Task\Transfer\RsyncTask;
use TYPO3\Surf\Tests\Unit\AssertCommandExecuted;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the RsyncTask
 */
class RsyncTaskTest extends BaseTaskTest
{
    /**
     * Set up test dependencies
     */
    protected function setUp()
    {
        parent::setUp();

        $this->application = new Flow('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeWithUsernameAndDefaultOptionsCreatesDirectoryAndTransfersAndCopiesFiles()
    {
        $this->node->setOption('hostname', 'myserver.local');
        $this->node->setOption('username', 'jdoe');

        $this->task->execute($this->node, $this->application, $this->deployment, []);

        $this->assertCommandExecuted('mkdir -p /home/jdoe/app/cache/transfer');
        $this->assertCommandExecuted('/rsync -q --compress --rsh="ssh"  --recursive --times --perms --links --delete --delete-excluded --exclude \'.git\' \'.*\/Data\/Surf\/TestDeployment\/TestApplication\/.\' \'jdoe@myserver.local:\/home\/jdoe\/app\/cache\/transfer\'/');
        $this->assertCommandExecuted('/cp -RPp \/home\/jdoe\/app\/cache\/transfer\/. \/home\/jdoe\/app\/releases\/[0-9]+/');
    }

    /**
     * @test
     */
    public function executeWithUsernameAndPasswordAndDefaultOptionsCreatesDirectoryAndTransfersAndCopiesFiles()
    {
        $this->node->setOption('hostname', 'myserver.local');
        $this->node->setOption('username', 'jdoe');
        $this->node->setOption('password', 'jdoe');

        $this->task->execute($this->node, $this->application, $this->deployment, []);

        $this->assertCommandExecuted('mkdir -p /home/jdoe/app/cache/transfer');
        $this->assertCommandExecuted('/rsync -q --compress --rsh="ssh -o PubkeyAuthentication=no"  --recursive --times --perms --links --delete --delete-excluded --exclude \'.git\' \'.*\/Data\/Surf\/TestDeployment\/TestApplication\/.\' \'jdoe@myserver.local:\/home\/jdoe\/app\/cache\/transfer\'/');
        $this->assertCommandExecuted('/cp -RPp \/home\/jdoe\/app\/cache\/transfer\/. \/home\/jdoe\/app\/releases\/[0-9]+/');
    }

    /**
     * @test
     */
    public function executeWithPrivateKeyAddsFlagToSshCommand()
    {
        $this->node->setOption('hostname', 'myserver.local');
        $this->node->setOption('username', 'jdoe');
        $this->node->setOption('privateKeyFile', '~/.ssh/foo');

        $this->task->execute($this->node, $this->application, $this->deployment, []);

        $this->assertCommandExecuted('mkdir -p /home/jdoe/app/cache/transfer');
        $this->assertCommandExecuted('/rsync -q --compress --rsh="ssh -i \'~\/.ssh\/foo\'"  --recursive --times --perms --links --delete --delete-excluded --exclude \'.git\' \'.*\/Data\/Surf\/TestDeployment\/TestApplication\/.\' \'jdoe@myserver.local:\/home\/jdoe\/app\/cache\/transfer\'/');
        $this->assertCommandExecuted('/cp -RPp \/home\/jdoe\/app\/cache\/transfer\/. \/home\/jdoe\/app\/releases\/[0-9]+/');
    }

    /**
     * @test
     */
    public function executeWithDefaultExcludeList()
    {
        $this->node->setOption('hostname', 'myserver.local');
        $options = [];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('/--recursive --times --perms --links --delete --delete-excluded --exclude \'.git\'/');
    }

    /**
     * @test
     */
    public function executeWithEmptyExcludeList()
    {
        $this->node->setOption('hostname', 'myserver.local');
        $options = [
            'rsyncExcludes' => []
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertThat(
            $this->commands['executed'],
            $this->logicalNot(
                new AssertCommandExecuted('/--exclude/')
            )
        );
    }

    /**
     * @test
     */
    public function executeWithCustomExcludeList()
    {
        $this->node->setOption('hostname', 'myserver.local');
        $options = [
            'rsyncExcludes' => [
                '.git',
                '.gitmodules',
                '/Deploy'
            ]
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('/--recursive --times --perms --links --delete --delete-excluded --exclude \'.git\' --exclude \'.gitmodules\' --exclude \'\/Deploy\'/');
    }

    /**
     * @test
     */
    public function executeWithCustomRsyncFlags()
    {
        $this->node->setOption('hostname', 'myserver.local');
        $options = [
            'rsyncFlags' => '--recursive --times --perms --links --delete --delete-excluded --append-verify'
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('/--recursive --times --perms --links --delete --delete-excluded --append-verify --exclude \'.git\'/');
    }

    /**
     * @test
     */
    public function executeWithCustomRsyncFlagsAndCustomExcludeList()
    {
        $this->node->setOption('hostname', 'myserver.local');
        $options = [
            'rsyncFlags' => '--recursive --times --perms --links --delete --delete-excluded --append-verify',
            'rsyncExcludes' => [
                '.git',
                '.gitmodules',
                '/Deploy'
            ]
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('/--recursive --times --perms --links --delete --delete-excluded --append-verify --exclude \'.git\' --exclude \'.gitmodules\' --exclude \'\/Deploy\'/');
    }

    /**
     * @test
     */
    public function executeWithoutUsernameDoesNotAppendUsernameToRsyncTarget()
    {
        $this->node->setOption('hostname', 'myserver.local');

        $this->task->execute($this->node, $this->application, $this->deployment, []);

        $this->assertCommandExecuted('/rsync .* \'myserver.local:\/home\/jdoe\/app\/cache\/transfer\'/');
    }

    /**
     * @test
     */
    public function executeWithTypo3Cms()
    {
        $this->application = new CMS();
        $this->node->setOption('hostname', 'myserver.local');
        $options = $this->application->getOptions();

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('/--recursive --times --perms --links --delete --delete-excluded --exclude \'.ddev\' --exclude \'.git\' --exclude \'web\/fileadmin\' --exclude \'web\/uploads\'/');
    }

    /**
     * @test
     */
    public function executeWithTypo3CmsAndCustomWebDirectory()
    {
        $this->application = new CMS();
        $this->application->setOption('webDirectory', 'public');
        $this->node->setOption('hostname', 'myserver.local');
        $options = $this->application->getOptions();

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('/--recursive --times --perms --links --delete --delete-excluded --exclude \'.ddev\' --exclude \'.git\' --exclude \'public\/fileadmin\' --exclude \'public\/uploads\'/');
    }

    /**
     * @return \TYPO3\Surf\Domain\Model\Task
     */
    protected function createTask()
    {
        return new RsyncTask();
    }
}
