<?php


namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Service\LocalShellReplacePathService;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

class LocalShellReplacePathServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var LocalShellReplacePathService
     */
    private $subject;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->subject = new LocalShellReplacePathService();
    }

    /**
     * @test
     * @dataProvider pathReplacementProvider
     */
    public function replacePaths($search, $replace)
    {
        /** @var Application|\PHPUnit_Framework_MockObject_MockObject $application */
        $application = $this->getMockBuilder('TYPO3\Surf\Domain\Model\Application')->disableOriginalConstructor()->getMock();

        /** @var Deployment|\PHPUnit_Framework_MockObject_MockObject $deployment */
        $deployment = $this->getMockBuilder('TYPO3\Surf\Domain\Model\Deployment')->disableOriginalConstructor()->getMock();
        $deployment->expects($this->once())->method('getWorkspacePath')->willReturn('workspace');

        $command = 'command {'.$search.'}';

        $command = $this->subject->replacePaths($command, $application, $deployment);
        $this->assertSame('command '.escapeshellarg($replace), $command);
    }

    /**
     * @return array
     */
    public function pathReplacementProvider()
    {
        return array(
            array('workspacePath', 'workspace'),
        );
    }

}
