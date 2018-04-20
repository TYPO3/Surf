<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

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
        $this->application = new \TYPO3\Surf\Application\TYPO3\CMS('TestApplication');
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
        $options = array();
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->application);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }");
        $this->assertCommandExecuted("{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }");
        $this->assertCommandExecuted("rm -rf '{$releasePath}'/fileadmin");
        $this->assertCommandExecuted("rm -rf '{$releasePath}'/uploads");
        $this->assertCommandExecuted("ln -sf {$dataPath}/fileadmin '{$releasePath}'/fileadmin");
        $this->assertCommandExecuted("ln -sf {$dataPath}/uploads '{$releasePath}'/uploads");
    }

    /**
     * @test
     */
    public function withAdditionalDirectoriesCreatesCorrectLinks()
    {
        $options = array(
            'directories' => array('pictures', 'test/assets'),
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->application);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }");
        $this->assertCommandExecuted("{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }");
        $this->assertCommandExecuted("rm -rf '{$releasePath}'/fileadmin");
        $this->assertCommandExecuted("rm -rf '{$releasePath}'/uploads");
        $this->assertCommandExecuted("ln -sf {$dataPath}/fileadmin '{$releasePath}'/fileadmin");
        $this->assertCommandExecuted("ln -sf {$dataPath}/uploads '{$releasePath}'/uploads");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/pictures' ] || mkdir -p '{$dataPath}/pictures' ; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/pictures' 'pictures'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/test/assets' ] || mkdir -p '{$dataPath}/test/assets' ; }");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/test/assets' 'test/assets'");
    }

    /**
     * @test
     */
    public function withApplicationRootCreatesCorrectLinks()
    {
        $options = array(
            'webDirectory' => 'web/'
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->application);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }");
        $this->assertCommandExecuted("{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }");
        $this->assertCommandExecuted("rm -rf '{$releasePath}/web'/fileadmin");
        $this->assertCommandExecuted("rm -rf '{$releasePath}/web'/uploads");
        $this->assertCommandExecuted("ln -sf ../{$dataPath}/fileadmin '{$releasePath}/web'/fileadmin");
        $this->assertCommandExecuted("ln -sf ../{$dataPath}/uploads '{$releasePath}/web'/uploads");
    }

    /**
     * @test
     */
    public function withAdditionalDirectoriesAndApplicationRootCreatesCorrectLinks()
    {
        $options = array(
            'webDirectory' => 'web/',
            'directories' => array('pictures', 'test/assets', '/withSlashes/'),
        );
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $releasePath = $this->deployment->getApplicationReleasePath($this->application);
        $dataPath = '../../shared/Data';
        $this->assertCommandExecuted("cd '{$releasePath}'");
        $this->assertCommandExecuted("{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }");
        $this->assertCommandExecuted("{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }");
        $this->assertCommandExecuted("rm -rf '{$releasePath}/web'/fileadmin");
        $this->assertCommandExecuted("rm -rf '{$releasePath}/web'/uploads");
        $this->assertCommandExecuted("ln -sf ../{$dataPath}/fileadmin '{$releasePath}/web'/fileadmin");
        $this->assertCommandExecuted("ln -sf ../{$dataPath}/uploads '{$releasePath}/web'/uploads");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/pictures' ] || mkdir -p '{$dataPath}/pictures' ; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/pictures' 'pictures'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/withSlashes' ] || mkdir -p '{$dataPath}/withSlashes' ; }");
        $this->assertCommandExecuted("ln -sf '{$dataPath}/withSlashes' 'withSlashes'");
        $this->assertCommandExecuted("{ [ -d '{$dataPath}/test/assets' ] || mkdir -p '{$dataPath}/test/assets' ; }");
        $this->assertCommandExecuted("ln -sf '../{$dataPath}/test/assets' 'test/assets'");
    }
}
