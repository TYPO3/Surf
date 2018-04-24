<?php


namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Service\ShellReplacePathCompositeService;
use TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

class ShellReplacePathCompositeServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ShellReplacePathCompositeService
     */
    private $subject;

    /**
     * @var ShellReplacePathServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localShellReplacePathService;

    /**
     * @var ShellReplacePathServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shellReplacePathService;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->localShellReplacePathService = $this->getMock('TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface');
        $this->shellReplacePathService = $this->getMock('TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface');
        $this->subject = new ShellReplacePathCompositeService(array($this->localShellReplacePathService, $this->shellReplacePathService));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function constructionFailsWrongArgumentsPassed()
    {
        $subject = new ShellReplacePathCompositeService(array('string', 1));
    }

    /**
     * @test
     */
    public function replacePaths()
    {
        /** @var Application|\PHPUnit_Framework_MockObject_MockObject $application */
        $application = $this->getMockBuilder('TYPO3\Surf\Domain\Model\Application')->disableOriginalConstructor()->getMock();

        /** @var Deployment|\PHPUnit_Framework_MockObject_MockObject $deployment */
        $deployment = $this->getMockBuilder('TYPO3\Surf\Domain\Model\Deployment')->disableOriginalConstructor()->getMock();

        $this->localShellReplacePathService->expects($this->once())->method('replacePaths')->willReturn('local shell command');
        $this->shellReplacePathService->expects($this->once())->method('replacePaths')->willReturn('shell command');

        $command = 'command';
        $command = $this->subject->replacePaths($command, $application, $deployment);
        $this->assertEquals('shell command', $command);
    }

}
