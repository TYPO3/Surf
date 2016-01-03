<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Service\ShellCommandService;
use TYPO3\Surf\Task\TYPO3\CMS\SymlinkDataTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Class SymlinkDataTest
 */
class SymlinkDataTaskTest extends BaseTaskTest
{
    /**
     * @var SymlinkDataTask
     */
    protected $task;

    /**
     * @var ShellCommandService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shellMock;

    /**
     * @var Node|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $nodeMock;

    /**
     * @var Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $applicationMock;

    /**
     * @var Deployment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deploymentMock;

    /**
     * @return SymlinkDataTask
     */
    protected function createTask()
    {
        $task = new SymlinkDataTask();
        $this->shellMock = $this->getMock('TYPO3\Surf\Domain\Service\ShellCommandService');
        $task->setShellCommandService($this->shellMock);
        return $task;
    }

    public function setUp()
    {
        $this->task = $this->createTask();
        $this->nodeMock = $this->getMockBuilder('TYPO3\Surf\Domain\Model\Node')->disableOriginalConstructor()->getMock();
        $this->deploymentMock = $this->getMockBuilder('TYPO3\Surf\Domain\Model\Deployment')->disableOriginalConstructor()->getMock();

        $this->deploymentMock->expects($this->once())
            ->method('getApplicationReleasePath')
            ->willReturn('/releases/current');

        $this->applicationMock = $this->getMockBuilder('TYPO3\Surf\Domain\Model\Application')->disableOriginalConstructor()->getMock();
    }

    /**
     * @test
     */
    public function withoutOptionsCreatesCorrectLinks()
    {
        $dataPath = '../../shared/Data';
        $expectedCommands = array(
            "cd '/releases/current'",
            "{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }",
            "{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }",
            "ln -sf {$dataPath}/fileadmin ./fileadmin",
            "ln -sf {$dataPath}/uploads ./uploads",
        );

        $options = array();

        $this->shellMock->expects($this->once())
            ->method('executeOrSimulate')
            ->with($expectedCommands, $this->nodeMock, $this->deploymentMock)
        ;

        $this->task->execute($this->nodeMock, $this->applicationMock, $this->deploymentMock, $options);
    }

    /**
     * @test
     */
    public function withAdditionalDirectoriesCreatesCorrectLinks()
    {
        $dataPath = '../../shared/Data';
        $expectedCommands = array(
            "cd '/releases/current'",
            "{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }",
            "{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }",
            "ln -sf {$dataPath}/fileadmin ./fileadmin",
            "ln -sf {$dataPath}/uploads ./uploads",
            "{ [ -d '{$dataPath}/pictures' ] || mkdir -p '{$dataPath}/pictures' ; }",
            "ln -sf '{$dataPath}/pictures' 'pictures'",
            "{ [ -d '{$dataPath}/test/assets' ] || mkdir -p '{$dataPath}/test/assets' ; }",
            "ln -sf '../{$dataPath}/test/assets' 'test/assets'",
        );

        $options = array(
            'directories' => array('pictures', 'test/assets'),
        );

        $this->shellMock->expects($this->once())
            ->method('executeOrSimulate')
            ->with($expectedCommands, $this->nodeMock, $this->deploymentMock)
        ;

        $this->task->execute($this->nodeMock, $this->applicationMock, $this->deploymentMock, $options);
    }

    /**
     * @test
     */
    public function withApplicationRootCreatesCorrectLinks()
    {
        $dataPath = '../../../../shared/Data';
        $expectedCommands = array(
            "cd '/releases/current/app/dir'",
            "{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }",
            "{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }",
            "ln -sf {$dataPath}/fileadmin ./fileadmin",
            "ln -sf {$dataPath}/uploads ./uploads",
        );

        $options = array(
            'applicationRootDirectory' => 'app/dir/'
        );

        $this->shellMock->expects($this->once())
            ->method('executeOrSimulate')
            ->with($expectedCommands, $this->nodeMock, $this->deploymentMock)
        ;

        $this->task->execute($this->nodeMock, $this->applicationMock, $this->deploymentMock, $options);
    }

    /**
     * @test
     */
    public function withAdditionalDirectoriesAndApplicationRootCreatesCorrectLinks()
    {
        $dataPath = '../../../../shared/Data';
        $expectedCommands = array(
            "cd '/releases/current/app/dir'",
            "{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }",
            "{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }",
            "ln -sf {$dataPath}/fileadmin ./fileadmin",
            "ln -sf {$dataPath}/uploads ./uploads",
            "{ [ -d '{$dataPath}/pictures' ] || mkdir -p '{$dataPath}/pictures' ; }",
            "ln -sf '{$dataPath}/pictures' 'pictures'",
            "{ [ -d '{$dataPath}/test/assets' ] || mkdir -p '{$dataPath}/test/assets' ; }",
            "ln -sf '../{$dataPath}/test/assets' 'test/assets'",
        );

        $options = array(
            'applicationRootDirectory' => 'app/dir/',
            'directories' => array('pictures', 'test/assets'),
        );

        $this->shellMock->expects($this->once())
            ->method('executeOrSimulate')
            ->with($expectedCommands, $this->nodeMock, $this->deploymentMock)
        ;

        $this->task->execute($this->nodeMock, $this->applicationMock, $this->deploymentMock, $options);
    }
}
