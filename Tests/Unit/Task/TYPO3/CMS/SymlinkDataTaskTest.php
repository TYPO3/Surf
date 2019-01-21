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

    protected function setUp()
    {
        parent::setUp();
        $this->application = new CMS('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
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
    public function withoutOptionsCreatesCorrectLinks()
    {
        $options = [
            'webDirectory' => '',
        ];

        $options = $this->mergeOptions($options);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->application);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/fileadmin' ] || mkdir -p '{$dataPath}/fileadmin'; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/fileadmin' '{$releasePath}/fileadmin'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/uploads' ] || mkdir -p '{$dataPath}/uploads'; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/uploads' '{$releasePath}/uploads'");
    }

    /**
     * @test
     */
    public function disableCreationOfUploadsFolder()
    {
        $options = [
            'webDirectory' => '',
            'symlinkDataFolders' => ['fileadmin']
        ];
        $options = $this->mergeOptions($options);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->application);
        $dataPath = '../../shared/Data';
        $this->assertNotContains("{ [ -d '{$dataPath}/uploads' ] || mkdir -p '{$dataPath}/uploads'; }", $this->commands['executed']);
        $this->assertNotContains("ln -sf '{$dataPath}/uploads' '{$releasePath}/uploads'", $this->commands['executed']);

        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/fileadmin' ] || mkdir -p '{$dataPath}/fileadmin'; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/fileadmin' '{$releasePath}/fileadmin'");
    }

    /**
     * @test
     */
    public function withAdditionalDirectoriesCreatesCorrectLinks()
    {
        $options = [
            'directories' => ['pictures', 'test/assets'],
            'webDirectory' => '',
        ];
        $options = $this->mergeOptions($options);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->application);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/fileadmin' ] || mkdir -p '{$dataPath}/fileadmin'; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/fileadmin' '{$releasePath}/fileadmin'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/uploads' ] || mkdir -p '{$dataPath}/uploads'; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/uploads' '{$releasePath}/uploads'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/pictures' ] || mkdir -p '{$dataPath}/pictures'; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/pictures' 'pictures'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/test/assets' ] || mkdir -p '{$dataPath}/test/assets'; }");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/test/assets' 'test/assets'");
    }

    /**
     * @test
     */
    public function withApplicationRootCreatesCorrectLinks()
    {
        $options = [
            'webDirectory' => 'web/'
        ];
        $options = $this->mergeOptions($options);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->application);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/fileadmin' ] || mkdir -p '{$dataPath}/fileadmin'; }");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/fileadmin' '{$releasePath}/web/fileadmin'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/uploads' ] || mkdir -p '{$dataPath}/uploads'; }");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/uploads' '{$releasePath}/web/uploads'");
    }

    /**
     * @test
     */
    public function withAdditionalDirectoriesAndApplicationRootCreatesCorrectLinks()
    {
        $options = [
            'webDirectory' => 'web/',
            'directories' => ['pictures', 'test/assets', '/withSlashes/'],
        ];
        $options = $this->mergeOptions($options);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->application);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/fileadmin' ] || mkdir -p '{$dataPath}/fileadmin'; }");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/fileadmin' '{$releasePath}/web/fileadmin'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/uploads' ] || mkdir -p '{$dataPath}/uploads'; }");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/uploads' '{$releasePath}/web/uploads'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/pictures' ] || mkdir -p '{$dataPath}/pictures'; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/pictures' 'pictures'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/withSlashes' ] || mkdir -p '{$dataPath}/withSlashes'; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/withSlashes' 'withSlashes'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/test/assets' ] || mkdir -p '{$dataPath}/test/assets'; }");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/test/assets' 'test/assets'");
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function mergeOptions(array $options)
    {
        $options = array_merge($this->application->getOptions(), $options);

        return $options;
    }
}
