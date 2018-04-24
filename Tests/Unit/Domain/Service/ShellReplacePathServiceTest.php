<?php


namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Service\ShellReplacePathService;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

class ShellReplacePathServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ShellReplacePathService
     */
    protected $subject;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->subject = new ShellReplacePathService();
    }


    /**
     * @test
     * @dataProvider pathReplacementProvider
     */
    public function replacePaths($search, $replace)
    {
        /** @var Application|\PHPUnit_Framework_MockObject_MockObject $application */
        $application = $this->getMockBuilder('TYPO3\Surf\Domain\Model\Application')->disableOriginalConstructor()->getMock();
        $application->expects($this->once())->method('getDeploymentPath')->willReturn('path');
        $application->expects($this->once())->method('getSharedPath')->willReturn('path/shared');
        $application->expects($this->exactly(2))->method('getReleasesPath')->willReturn('path/releases');

        /** @var Deployment|\PHPUnit_Framework_MockObject_MockObject $deployment */
        $deployment = $this->getMockBuilder('TYPO3\Surf\Domain\Model\Deployment')->disableOriginalConstructor()->getMock();
        $deployment->expects($this->once())->method('getApplicationReleasePath')->willReturn('path/releases/11111111');

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
            array('deploymentPath', 'path'),
            array('sharedPath', 'path/shared'),
            array('releasePath', 'path/releases/11111111'),
            array('currentPath', 'path/releases/current'),
            array('previousPath', 'path/releases/previous'),
        );
    }

}
