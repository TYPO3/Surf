<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Task\TYPO3\CMS\SymlinkDataTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Class SymlinkDataTaskTest
 */
class SymlinkDataTaskTest extends BaseTaskTest
{
    /**
     * @var SymlinkDataTask
     */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @return SymlinkDataTask
     */
    protected function createTask()
    {
        return new SymlinkDataTask();
    }

    /**
     * @test
     */
    public function withoutOptionsCreatesCorrectLinks(): void
    {
        $options = [
            'webDirectory' => '',
        ];

        $options = $this->mergeOptions($options);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->node);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/fileadmin'");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/fileadmin' '{$releasePath}/fileadmin'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/uploads'");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/uploads' '{$releasePath}/uploads'");
    }

    /**
     * @test
     */
    public function disableCreationOfUploadsFolder(): void
    {
        $options = [
            'webDirectory' => '',
            'symlinkDataFolders' => ['fileadmin']
        ];
        $options = $this->mergeOptions($options);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->node);
        $dataPath = '../../shared/Data';
        self::assertNotContains("mkdir -p '{$dataPath}/uploads'", $this->commands['executed']);
        self::assertNotContains("ln -sf '{$dataPath}/uploads' '{$releasePath}/uploads'", $this->commands['executed']);

        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/fileadmin'");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/fileadmin' '{$releasePath}/fileadmin'");
    }

    /**
     * @test
     */
    public function withAdditionalDirectoriesCreatesCorrectLinks(): void
    {
        $options = [
            'directories' => ['pictures', 'test/assets'],
            'webDirectory' => '',
        ];
        $options = $this->mergeOptions($options);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->node);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/fileadmin'");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/fileadmin' '{$releasePath}/fileadmin'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/uploads'");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/uploads' '{$releasePath}/uploads'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/pictures'");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/pictures' 'pictures'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/test/assets'");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/test/assets' 'test/assets'");
    }

    /**
     * @test
     */
    public function withApplicationRootCreatesCorrectLinks(): void
    {
        $options = [
            'webDirectory' => 'web/'
        ];
        $options = $this->mergeOptions($options);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->node);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/fileadmin'");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/fileadmin' '{$releasePath}/web/fileadmin'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/uploads'");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/uploads' '{$releasePath}/web/uploads'");
    }

    /**
     * @test
     */
    public function withAdditionalDirectoriesAndApplicationRootCreatesCorrectLinks(): void
    {
        $options = [
            'webDirectory' => 'web/',
            'directories' => ['pictures', 'test/assets', '/withSlashes/'],
        ];
        $options = $this->mergeOptions($options);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->node);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/fileadmin'");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/fileadmin' '{$releasePath}/web/fileadmin'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/uploads'");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/uploads' '{$releasePath}/web/uploads'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/pictures'");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/pictures' 'pictures'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/withSlashes'");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/withSlashes' 'withSlashes'");
        $this->assertCommandExecuted("mkdir -p '{$dataPath}/test/assets'");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/test/assets' 'test/assets'");
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function mergeOptions(array $options)
    {
        return array_merge($this->application->getOptions(), $options);
    }
}
