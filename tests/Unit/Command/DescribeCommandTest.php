<?php

namespace TYPO3\Surf\Tests\Unit\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Application\Neos\Neos;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Command\DescribeCommand;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception;
use TYPO3\Surf\Integration\FactoryInterface;
use TYPO3\Surf\Task\LocalShellTask;
use TYPO3\Surf\Task\Transfer\RsyncTask;
use TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask;
use TYPO3\Surf\Tests\Unit\KernelAwareTrait;

class DescribeCommandTest extends TestCase
{
    use KernelAwareTrait;

    /**
     * @var Deployment
     */
    protected $deployment;

    /**
     * @var Application|BaseApplication
     */
    protected $application;

    /**
     * @var Node
     */
    protected $node;

    protected function setUp(): void
    {
        $this->deployment = new Deployment('TestDeployment');
        $this->deployment->setContainer(static::getKernel()->getContainer());

        $this->node = new Node('TestNode');
        $this->node->setHostname('hostname');
    }

    protected function setUpCustomApplication(): void
    {
        $this->application = new Application('TestApplication');
        $this->application->setOption('rsyncExcludes', ['.git', 'web/fileadmin', 'web/uploads']);
        $this->application->setOption(RsyncTask::class . '[rsyncExcludes]', ['.git', 'web/fileadmin', 'web/uploads']);
        $this->application->addNode($this->node);
        $this->deployment->addApplication($this->application);
        $this->deployment->onInitialize(function () {
            $workflow = $this->deployment->getWorkflow();
            $workflow->defineTask('TYPO3\\Surf\\Task\\CustomTask', LocalShellTask::class, [
                'command' => [
                    'touch test.txt',
                ],
            ]);
            $workflow->defineTask('TYPO3\\Surf\\Task\\OtherCustomTask', LocalShellTask::class, [
                'command' => [
                    'touch test.txt',
                ],
            ]);
            $workflow->defineTask('TYPO3\\Surf\\Task\\TaskForOneApp', LocalShellTask::class, [
                'command' => [
                    'touch test.txt',
                ],
            ]);
            $workflow->defineTask('TYPO3\\Surf\\Task\\TaskForAllAppsAfterTaskForOneApp', LocalShellTask::class, [
                'command' => [
                    'touch test.txt',
                ],
            ]);
            $workflow->defineTask('TYPO3\\Surf\\Task\\TaskForAllApps', LocalShellTask::class, [
                'command' => [
                    'touch test.txt',
                ],
            ]);
            $workflow->defineTask('TYPO3\\Surf\\Task\\TaskForOneAppAfterTaskForAllApps', LocalShellTask::class, [
                'command' => [
                    'touch test.txt',
                ],
            ]);
            $workflow->addTask(FlushCachesTask::class, 'finalize');
            $workflow->afterTask(FlushCachesTask::class, 'TYPO3\\Surf\\Task\\CustomTask');
            $workflow->beforeTask(FlushCachesTask::class, 'TYPO3\\Surf\\Task\\CustomTask');

            $workflow->addTask('TYPO3\\Surf\\Task\\TaskForOneApp', 'package', $this->application);
            $workflow->afterTask('TYPO3\\Surf\\Task\\TaskForOneApp', 'TYPO3\\Surf\\Task\\TaskForAllAppsAfterTaskForOneApp');

            $workflow->addTask('TYPO3\\Surf\\Task\\TaskForAllApps', 'transfer');
            $workflow->afterTask('TYPO3\\Surf\\Task\\TaskForAllApps', 'TYPO3\\Surf\\Task\\TaskForOneAppAfterTaskForAllApps', $this->application);
        });
        $this->deployment->initialize();
    }

    /**
     * @test
     * @throws Exception
     */
    public function describeCustomApplication(): void
    {
        $this->setUpCustomApplication();
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('getDeployment')->willReturn($this->deployment);
        $command = new DescribeCommand($factory);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'deploymentName' => $this->deployment->getName(),
        ]);

        self::assertEquals('<success>Deployment TestDeployment</success>

Workflow: <success>Simple workflow</success>
    Rollback enabled: true

Nodes:

  <success>TestNode</success> (hostname)

Applications:

  <success>TestApplication:</success>
    Deployment path: <success></success>
    Options:
      rsyncExcludes =>
        <success>.git</success>
        <success>web/fileadmin</success>
        <success>web/uploads</success>
      TYPO3\Surf\Task\Transfer\RsyncTask[rsyncExcludes] =>
        <success>.git</success>
        <success>web/fileadmin</success>
        <success>web/uploads</success>
    Nodes: <success>TestNode</success>
    Detailed workflow:
      initialize:
      lock:
      package:
        tasks:
          <success>TYPO3\Surf\Task\TaskForOneApp</success> (for application TestApplication)
          <success>Task TYPO3\Surf\Task\TaskForAllAppsAfterTaskForOneApp after TYPO3\Surf\Task\TaskForOneApp</success> (for all applications)
      transfer:
        tasks:
          <success>TYPO3\Surf\Task\TaskForAllApps</success> (for all applications)
          <success>Task TYPO3\Surf\Task\TaskForOneAppAfterTaskForAllApps after TYPO3\Surf\Task\TaskForAllApps</success> (for application TestApplication)
      update:
      migrate:
      finalize:
        tasks:
          <success>Task TYPO3\Surf\Task\CustomTask before TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask</success> (for all applications)
          <success>TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask</success> (for all applications)
          <success>Task TYPO3\Surf\Task\CustomTask after TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask</success> (for all applications)
      test:
      switch:
      cleanup:
      unlock:
', $commandTester->getDisplay());
    }

    /**
     * @param Application $application
     * @param array $options
     * @return string
     * @throws Exception
     */
    protected function getDescriptionOfPredefinedApplication($application, $options = []): string
    {
        $this->application = $application;
        $this->application->addNode($this->node);
        foreach ($options as $option => $value) {
            $this->application->setOption($option, $value);
        }
        $this->deployment->addApplication($this->application)->initialize();
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('getDeployment')->willReturn($this->deployment);
        $command = new DescribeCommand($factory);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'deploymentName' => $this->deployment->getName(),
        ]);
        return $commandTester->getDisplay();
    }

    /**
     * @test
     */
    public function describeTypo3Cms(): void
    {
        $application = new CMS();
        $application->addSymlink(
            'public/typo3conf/LocalConfiguration.php',
            '../../../../shared/Configuration/LocalConfiguration.php'
        );
        self::assertEquals('<success>Deployment TestDeployment</success>

Workflow: <success>Simple workflow</success>
    Rollback enabled: true

Nodes:

  <success>TestNode</success> (hostname)

Applications:

  <success>TYPO3 CMS:</success>
    Deployment path: <success></success>
    Options:
      packageMethod => <success>git</success>
      transferMethod => <success>rsync</success>
      updateMethod => <success>NULL</success>
      lockDeployment => <success>1</success>
      webDirectory => <success>public</success>
      context => <success>Production</success>
      scriptFileName => <success>vendor/bin/typo3cms</success>
      symlinkDataFolders =>
        <success>fileadmin</success>
        <success>uploads</success>
      rsyncExcludes =>
        <success>.ddev</success>
        <success>.git</success>
        <success>{webDirectory}/fileadmin</success>
        <success>{webDirectory}/uploads</success>
      TYPO3\Surf\Task\Generic\CreateDirectoriesTask[directories] =>
      TYPO3\Surf\Task\Generic\CreateSymlinksTask[symlinks] =>
        <success>public/typo3conf/LocalConfiguration.php => ../../../../shared/Configuration/LocalConfiguration.php</success>
    Nodes: <success>TestNode</success>
    Detailed workflow:
      initialize:
        tasks:
          <success>TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application TYPO3 CMS)
          <success>Task TYPO3\Surf\Task\Generic\CreateDirectoriesTask after TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application TYPO3 CMS)
      lock:
        tasks:
          <success>TYPO3\Surf\Task\LockDeploymentTask</success> (for application TYPO3 CMS)
      package:
        tasks:
          <success>TYPO3\Surf\Task\Package\GitTask</success> (for application TYPO3 CMS)
          <success>Task TYPO3\Surf\DefinedTask\Composer\LocalInstallTask after TYPO3\Surf\Task\Package\GitTask</success> (for application TYPO3 CMS)
      transfer:
        tasks:
          <success>TYPO3\Surf\Task\Transfer\RsyncTask</success> (for application TYPO3 CMS)
        after:
          <success>TYPO3\Surf\Task\Generic\CreateSymlinksTask</success> (for application TYPO3 CMS)
      update:
        after:
          <success>TYPO3\Surf\Task\TYPO3\CMS\SymlinkDataTask</success> (for application TYPO3 CMS)
      migrate:
        tasks:
          <success>TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask</success> (for application TYPO3 CMS)
      finalize:
      test:
      switch:
        tasks:
          <success>TYPO3\Surf\Task\SymlinkReleaseTask</success> (for application TYPO3 CMS)
        after:
          <success>TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask</success> (for application TYPO3 CMS)
      cleanup:
        tasks:
          <success>TYPO3\Surf\Task\CleanupReleasesTask</success> (for application TYPO3 CMS)
      unlock:
        tasks:
          <success>TYPO3\Surf\Task\UnlockDeploymentTask</success> (for application TYPO3 CMS)
', $this->getDescriptionOfPredefinedApplication($application));
    }

    /**
     * @test
     */
    public function describeNeosNeos(): void
    {
        self::assertEquals('<success>Deployment TestDeployment</success>

Workflow: <success>Simple workflow</success>
    Rollback enabled: true

Nodes:

  <success>TestNode</success> (hostname)

Applications:

  <success>Neos:</success>
    Deployment path: <success></success>
    Options:
      packageMethod => <success>git</success>
      transferMethod => <success>rsync</success>
      updateMethod => <success>NULL</success>
      lockDeployment => <success>1</success>
      webDirectory => <success>Web</success>
      enableCacheWarmupBeforeSwitchingToNewRelease => <success></success>
      enableCacheWarmupAfterSwitchingToNewRelease => <success></success>
      TYPO3\Surf\Task\Generic\CreateDirectoriesTask[directories] =>
      TYPO3\Surf\Task\Generic\CreateSymlinksTask[symlinks] =>
    Nodes: <success>TestNode</success>
    Detailed workflow:
      initialize:
        tasks:
          <success>TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application Neos)
          <success>Task TYPO3\Surf\Task\Generic\CreateDirectoriesTask after TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application Neos)
          <success>TYPO3\Surf\Task\Neos\Flow\CreateDirectoriesTask</success> (for application Neos)
      lock:
        tasks:
          <success>TYPO3\Surf\Task\LockDeploymentTask</success> (for application Neos)
      package:
        tasks:
          <success>TYPO3\Surf\Task\Package\GitTask</success> (for application Neos)
          <success>Task TYPO3\Surf\DefinedTask\Composer\LocalInstallTask after TYPO3\Surf\Task\Package\GitTask</success> (for application Neos)
      transfer:
        tasks:
          <success>TYPO3\Surf\Task\Transfer\RsyncTask</success> (for application Neos)
        after:
          <success>TYPO3\Surf\Task\Generic\CreateSymlinksTask</success> (for application Neos)
      update:
        after:
          <success>TYPO3\Surf\Task\Neos\Flow\SymlinkDataTask</success> (for application Neos)
          <success>TYPO3\Surf\Task\Neos\Flow\SymlinkConfigurationTask</success> (for application Neos)
          <success>TYPO3\Surf\Task\Neos\Flow\CopyConfigurationTask</success> (for application Neos)
      migrate:
        tasks:
          <success>TYPO3\Surf\Task\Neos\Flow\MigrateTask</success> (for application Neos)
      finalize:
        tasks:
          <success>TYPO3\Surf\Task\Neos\Flow\PublishResourcesTask</success> (for application Neos)
      test:
      switch:
        tasks:
          <success>TYPO3\Surf\Task\SymlinkReleaseTask</success> (for application Neos)
      cleanup:
        tasks:
          <success>TYPO3\Surf\Task\CleanupReleasesTask</success> (for application Neos)
      unlock:
        tasks:
          <success>TYPO3\Surf\Task\UnlockDeploymentTask</success> (for application Neos)
', $this->getDescriptionOfPredefinedApplication(new Neos()));
    }

    /**
     * @test
     */
    public function describeNeosNeosWithWarmUpBeforeSymlink(): void
    {
        $application = new Neos();
        $application->setOption('enableCacheWarmupBeforeSwitchingToNewRelease', true);

        self::assertEquals('<success>Deployment TestDeployment</success>

Workflow: <success>Simple workflow</success>
    Rollback enabled: true

Nodes:

  <success>TestNode</success> (hostname)

Applications:

  <success>Neos:</success>
    Deployment path: <success></success>
    Options:
      packageMethod => <success>git</success>
      transferMethod => <success>rsync</success>
      updateMethod => <success>NULL</success>
      lockDeployment => <success>1</success>
      webDirectory => <success>Web</success>
      enableCacheWarmupBeforeSwitchingToNewRelease => <success>1</success>
      enableCacheWarmupAfterSwitchingToNewRelease => <success></success>
      TYPO3\Surf\Task\Generic\CreateDirectoriesTask[directories] =>
      TYPO3\Surf\Task\Generic\CreateSymlinksTask[symlinks] =>
    Nodes: <success>TestNode</success>
    Detailed workflow:
      initialize:
        tasks:
          <success>TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application Neos)
          <success>Task TYPO3\Surf\Task\Generic\CreateDirectoriesTask after TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application Neos)
          <success>TYPO3\Surf\Task\Neos\Flow\CreateDirectoriesTask</success> (for application Neos)
      lock:
        tasks:
          <success>TYPO3\Surf\Task\LockDeploymentTask</success> (for application Neos)
      package:
        tasks:
          <success>TYPO3\Surf\Task\Package\GitTask</success> (for application Neos)
          <success>Task TYPO3\Surf\DefinedTask\Composer\LocalInstallTask after TYPO3\Surf\Task\Package\GitTask</success> (for application Neos)
      transfer:
        tasks:
          <success>TYPO3\Surf\Task\Transfer\RsyncTask</success> (for application Neos)
        after:
          <success>TYPO3\Surf\Task\Generic\CreateSymlinksTask</success> (for application Neos)
      update:
        after:
          <success>TYPO3\Surf\Task\Neos\Flow\SymlinkDataTask</success> (for application Neos)
          <success>TYPO3\Surf\Task\Neos\Flow\SymlinkConfigurationTask</success> (for application Neos)
          <success>TYPO3\Surf\Task\Neos\Flow\CopyConfigurationTask</success> (for application Neos)
      migrate:
        tasks:
          <success>TYPO3\Surf\Task\Neos\Flow\MigrateTask</success> (for application Neos)
      finalize:
        tasks:
          <success>TYPO3\Surf\Task\Neos\Flow\PublishResourcesTask</success> (for application Neos)
          <success>TYPO3\Surf\Task\Neos\Flow\WarmUpCacheTask</success> (for application Neos)
      test:
      switch:
        tasks:
          <success>TYPO3\Surf\Task\SymlinkReleaseTask</success> (for application Neos)
      cleanup:
        tasks:
          <success>TYPO3\Surf\Task\CleanupReleasesTask</success> (for application Neos)
      unlock:
        tasks:
          <success>TYPO3\Surf\Task\UnlockDeploymentTask</success> (for application Neos)
', $this->getDescriptionOfPredefinedApplication($application));
    }

    /**
     * @test
     */
    public function describeNeosNeosWithWarmUpAfterSymlink(): void
    {
        $application = new Neos();
        $application->setOption('enableCacheWarmupAfterSwitchingToNewRelease', true);

        self::assertEquals('<success>Deployment TestDeployment</success>

Workflow: <success>Simple workflow</success>
    Rollback enabled: true

Nodes:

  <success>TestNode</success> (hostname)

Applications:

  <success>Neos:</success>
    Deployment path: <success></success>
    Options:
      packageMethod => <success>git</success>
      transferMethod => <success>rsync</success>
      updateMethod => <success>NULL</success>
      lockDeployment => <success>1</success>
      webDirectory => <success>Web</success>
      enableCacheWarmupBeforeSwitchingToNewRelease => <success></success>
      enableCacheWarmupAfterSwitchingToNewRelease => <success>1</success>
      TYPO3\Surf\Task\Generic\CreateDirectoriesTask[directories] =>
      TYPO3\Surf\Task\Generic\CreateSymlinksTask[symlinks] =>
    Nodes: <success>TestNode</success>
    Detailed workflow:
      initialize:
        tasks:
          <success>TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application Neos)
          <success>Task TYPO3\Surf\Task\Generic\CreateDirectoriesTask after TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application Neos)
          <success>TYPO3\Surf\Task\Neos\Flow\CreateDirectoriesTask</success> (for application Neos)
      lock:
        tasks:
          <success>TYPO3\Surf\Task\LockDeploymentTask</success> (for application Neos)
      package:
        tasks:
          <success>TYPO3\Surf\Task\Package\GitTask</success> (for application Neos)
          <success>Task TYPO3\Surf\DefinedTask\Composer\LocalInstallTask after TYPO3\Surf\Task\Package\GitTask</success> (for application Neos)
      transfer:
        tasks:
          <success>TYPO3\Surf\Task\Transfer\RsyncTask</success> (for application Neos)
        after:
          <success>TYPO3\Surf\Task\Generic\CreateSymlinksTask</success> (for application Neos)
      update:
        after:
          <success>TYPO3\Surf\Task\Neos\Flow\SymlinkDataTask</success> (for application Neos)
          <success>TYPO3\Surf\Task\Neos\Flow\SymlinkConfigurationTask</success> (for application Neos)
          <success>TYPO3\Surf\Task\Neos\Flow\CopyConfigurationTask</success> (for application Neos)
      migrate:
        tasks:
          <success>TYPO3\Surf\Task\Neos\Flow\MigrateTask</success> (for application Neos)
      finalize:
        tasks:
          <success>TYPO3\Surf\Task\Neos\Flow\PublishResourcesTask</success> (for application Neos)
      test:
      switch:
        tasks:
          <success>TYPO3\Surf\Task\SymlinkReleaseTask</success> (for application Neos)
          <success>Task TYPO3\Surf\Task\Neos\Flow\WarmUpCacheTask after TYPO3\Surf\Task\SymlinkReleaseTask</success> (for application Neos)
      cleanup:
        tasks:
          <success>TYPO3\Surf\Task\CleanupReleasesTask</success> (for application Neos)
      unlock:
        tasks:
          <success>TYPO3\Surf\Task\UnlockDeploymentTask</success> (for application Neos)
', $this->getDescriptionOfPredefinedApplication($application));
    }

    /**
     * @test
     */
    public function describeBaseApplication(): void
    {
        self::assertEquals('<success>Deployment TestDeployment</success>

Workflow: <success>Simple workflow</success>
    Rollback enabled: true

Nodes:

  <success>TestNode</success> (hostname)

Applications:

  <success>My App:</success>
    Deployment path: <success></success>
    Options:
      packageMethod => <success>git</success>
      transferMethod => <success>rsync</success>
      updateMethod => <success>NULL</success>
      lockDeployment => <success>1</success>
      webDirectory => <success>public</success>
      TYPO3\Surf\Task\Generic\CreateDirectoriesTask[directories] =>
      TYPO3\Surf\Task\Generic\CreateSymlinksTask[symlinks] =>
    Nodes: <success>TestNode</success>
    Detailed workflow:
      initialize:
        tasks:
          <success>TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application My App)
          <success>Task TYPO3\Surf\Task\Generic\CreateDirectoriesTask after TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application My App)
      lock:
        tasks:
          <success>TYPO3\Surf\Task\LockDeploymentTask</success> (for application My App)
      package:
        tasks:
          <success>TYPO3\Surf\Task\Package\GitTask</success> (for application My App)
          <success>Task TYPO3\Surf\DefinedTask\Composer\LocalInstallTask after TYPO3\Surf\Task\Package\GitTask</success> (for application My App)
      transfer:
        tasks:
          <success>TYPO3\Surf\Task\Transfer\RsyncTask</success> (for application My App)
        after:
          <success>TYPO3\Surf\Task\Generic\CreateSymlinksTask</success> (for application My App)
      update:
      migrate:
      finalize:
      test:
      switch:
        tasks:
          <success>TYPO3\Surf\Task\SymlinkReleaseTask</success> (for application My App)
      cleanup:
        tasks:
          <success>TYPO3\Surf\Task\CleanupReleasesTask</success> (for application My App)
      unlock:
        tasks:
          <success>TYPO3\Surf\Task\UnlockDeploymentTask</success> (for application My App)
', $this->getDescriptionOfPredefinedApplication(new BaseApplication('My App')));
    }

    /**
     * @test
     */
    public function describeBaseApplicationWithoutLock(): void
    {
        self::assertEquals('<success>Deployment TestDeployment</success>

Workflow: <success>Simple workflow</success>
    Rollback enabled: true

Nodes:

  <success>TestNode</success> (hostname)

Applications:

  <success>My App:</success>
    Deployment path: <success></success>
    Options:
      packageMethod => <success>git</success>
      transferMethod => <success>rsync</success>
      updateMethod => <success>NULL</success>
      lockDeployment => <success></success>
      webDirectory => <success>public</success>
      TYPO3\Surf\Task\Generic\CreateDirectoriesTask[directories] =>
      TYPO3\Surf\Task\Generic\CreateSymlinksTask[symlinks] =>
    Nodes: <success>TestNode</success>
    Detailed workflow:
      initialize:
        tasks:
          <success>TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application My App)
          <success>Task TYPO3\Surf\Task\Generic\CreateDirectoriesTask after TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application My App)
      lock:
      package:
        tasks:
          <success>TYPO3\Surf\Task\Package\GitTask</success> (for application My App)
          <success>Task TYPO3\Surf\DefinedTask\Composer\LocalInstallTask after TYPO3\Surf\Task\Package\GitTask</success> (for application My App)
      transfer:
        tasks:
          <success>TYPO3\Surf\Task\Transfer\RsyncTask</success> (for application My App)
        after:
          <success>TYPO3\Surf\Task\Generic\CreateSymlinksTask</success> (for application My App)
      update:
      migrate:
      finalize:
      test:
      switch:
        tasks:
          <success>TYPO3\Surf\Task\SymlinkReleaseTask</success> (for application My App)
      cleanup:
        tasks:
          <success>TYPO3\Surf\Task\CleanupReleasesTask</success> (for application My App)
      unlock:
', $this->getDescriptionOfPredefinedApplication(new BaseApplication('My App'), ['lockDeployment' => false]));
        self::assertEquals(false, $this->application->getOption('lockDeployment'));
    }

    /**
     * @test
     */
    public function describeBaseApplicationWithForceParameter(): void
    {
        $this->deployment->setForceRun(true);
        self::assertEquals('<success>Deployment TestDeployment</success>

Workflow: <success>Simple workflow</success>
    Rollback enabled: true

Nodes:

  <success>TestNode</success> (hostname)

Applications:

  <success>My App:</success>
    Deployment path: <success></success>
    Options:
      packageMethod => <success>git</success>
      transferMethod => <success>rsync</success>
      updateMethod => <success>NULL</success>
      lockDeployment => <success>1</success>
      webDirectory => <success>public</success>
      TYPO3\Surf\Task\Generic\CreateDirectoriesTask[directories] =>
      TYPO3\Surf\Task\Generic\CreateSymlinksTask[symlinks] =>
    Nodes: <success>TestNode</success>
    Detailed workflow:
      initialize:
        tasks:
          <success>TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application My App)
          <success>Task TYPO3\Surf\Task\Generic\CreateDirectoriesTask after TYPO3\Surf\Task\CreateDirectoriesTask</success> (for application My App)
      lock:
        tasks:
          <success>Task TYPO3\Surf\Task\UnlockDeploymentTask before TYPO3\Surf\Task\LockDeploymentTask</success> (for application My App)
          <success>TYPO3\Surf\Task\LockDeploymentTask</success> (for application My App)
      package:
        tasks:
          <success>TYPO3\Surf\Task\Package\GitTask</success> (for application My App)
          <success>Task TYPO3\Surf\DefinedTask\Composer\LocalInstallTask after TYPO3\Surf\Task\Package\GitTask</success> (for application My App)
      transfer:
        tasks:
          <success>TYPO3\Surf\Task\Transfer\RsyncTask</success> (for application My App)
        after:
          <success>TYPO3\Surf\Task\Generic\CreateSymlinksTask</success> (for application My App)
      update:
      migrate:
      finalize:
      test:
      switch:
        tasks:
          <success>TYPO3\Surf\Task\SymlinkReleaseTask</success> (for application My App)
      cleanup:
        tasks:
          <success>TYPO3\Surf\Task\CleanupReleasesTask</success> (for application My App)
      unlock:
        tasks:
          <success>TYPO3\Surf\Task\UnlockDeploymentTask</success> (for application My App)
', $this->getDescriptionOfPredefinedApplication(new BaseApplication('My App')));
    }
}
