<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Unit test for the ShellCommandService
 */
class ShellCommandServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test, if the given options are respected in executed SSH command
     *
     * @test
     * @dataProvider commandOptionDataProvider
     * @param string $expectedCommandArguments
     * @param string $username
     * @param string $password
     * @param int $port
     */
    public function executeRemoteCommandRespectsOptionsInSshCommand($expectedCommandArguments, $username = null, $password = null, $port = null)
    {
        /** @var \TYPO3\Surf\Domain\Service\ShellCommandService|\PHPUnit_Framework_MockObject_MockObject $service */
        $service = $this->getMock('TYPO3\Surf\Domain\Service\ShellCommandService', array('executeProcess'));

        $node = new \TYPO3\Surf\Domain\Model\Node('TestNode');
        $node->setHostname('remote-host.example.com');
        if ($username !== null) {
            $node->setOption('username', $username);
        }

        if ($password !== null) {
            $node->setOption('password', $password);
        }

        if ($port !== null) {
            $node->setOption('port', $port);
        }
        $deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $mockLogger = $this->getMock('Psr\Log\LoggerInterface');
        $deployment->setLogger($mockLogger);

        $expectedCommand = $expectedCommandArguments .  ' \'echo "Hello World"\'';
        $service->expects($this->once())->method('executeProcess')->with($this->anything(), $expectedCommand)->will($this->returnValue(array(0, 'Hello World')));

        $service->executeOrSimulate('echo "Hello World"', $node, $deployment);
    }

    /**
     * Data provider for executeRemoteCommandRespectsOptionsInSshCommand
     *
     * @return array
     */
    public function commandOptionDataProvider()
    {
        $resourcesPath = realpath(__DIR__ . '/../../../../Resources');
        return array(
            array(
                'ssh -A \'remote-host.example.com\'',
                null,
                null,
                null
            ),
            array(
                'ssh -A \'jdoe@remote-host.example.com\'',
                'jdoe',
                null,
                null
            ),
            array(
                'ssh -A -p \'12345\' \'jdoe@remote-host.example.com\'',
                'jdoe',
                null,
                12345
            ),
            array(
                'expect \'' . $resourcesPath . '/Private/Scripts/PasswordSshLogin.expect\' \'myPassword\' ssh -A -o PubkeyAuthentication=no \'jdoe@remote-host.example.com\'',
                'jdoe',
                'myPassword',
                null
            ),
        );
    }

    /**
     * @test
     */
    public function executeRemoteCommandRespectsRemoteCommandExecutionHandler()
    {
        $shellCommandService = new \TYPO3\Surf\Domain\Service\ShellCommandService();

        $node = new \TYPO3\Surf\Domain\Model\Node('TestNode');
        $node->setHostname('asdf');
        $arguments = array();

        $node->setOption('remoteCommandExecutionHandler', function (\TYPO3\Surf\Domain\Service\ShellCommandService $shellCommandService, $command, \TYPO3\Surf\Domain\Model\Node $node, \TYPO3\Surf\Domain\Model\Deployment $deployment, $logOutput) use (&$arguments) {
            $arguments = func_get_args();
            return array(0, 'Hello World');
        });

        $deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
        $mockLogger = $this->getMock('Psr\Log\LoggerInterface');
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $deployment->setLogger($mockLogger);

        $response = $shellCommandService->execute('foo command', $node, $deployment);
        $this->assertEquals('Hello World', $response);
        $this->assertSame(array(
            $shellCommandService,
            'foo command',
            $node,
            $deployment,
            true
        ), $arguments);
    }

    /**
     * @test
     */
    public function executeOnRemoteNodeJoinsCommandsWithAndOperator()
    {
        /** @var \TYPO3\Surf\Domain\Service\ShellCommandService|\PHPUnit_Framework_MockObject_MockObject $shellCommandService */
        $shellCommandService = $this->getMock('TYPO3\Surf\Domain\Service\ShellCommandService', array('executeProcess'));

        $node = new \TYPO3\Surf\Domain\Model\Node('TestNode');
        $node->setHostname('asdf');

        $deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $mockLogger = $this->getMock('Psr\Log\LoggerInterface');
        $deployment->setLogger($mockLogger);

        $shellCommandService->expects($this->any())->method('executeProcess')->with(
            $deployment, $this->stringContains('bin/false && ls -al')
        )->will($this->returnValue(array(0, 'Foo')));

        $response = $shellCommandService->execute(array('bin/false', 'ls -al'), $node, $deployment);

        $this->assertEquals('Foo', $response);
    }

    /**
     * @test
     */
    public function executeOnLocalNodeJoinsCommandsWithAndOperator()
    {
        /** @var \TYPO3\Surf\Domain\Service\ShellCommandService|\PHPUnit_Framework_MockObject_MockObject $shellCommandService */
        $shellCommandService = $this->getMock('TYPO3\Surf\Domain\Service\ShellCommandService', array('executeProcess'));

        $node = new \TYPO3\Surf\Domain\Model\Node('TestNode');
        $node->setHostname('localhost');

        $deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $mockLogger = $this->getMock('Psr\Log\LoggerInterface');
        $deployment->setLogger($mockLogger);

        $shellCommandService->expects($this->any())->method('executeProcess')->with(
            $deployment, $this->stringContains('bin/false && ls -al')
        )->will($this->returnValue(array(0, 'Foo')));

        $response = $shellCommandService->execute(array('bin/false', 'ls -al'), $node, $deployment);

        $this->assertEquals('Foo', $response);
    }

    /**
     * @test
     */
    public function executeProcessProperlyLogsStandardAndErrorOutput()
    {
        $shellCommandService = new \TYPO3\Surf\Domain\Service\ShellCommandService();
        $deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $mockLogger = $this->getMock('Psr\Log\LoggerInterface');
        $deployment->setLogger($mockLogger);

        $mockLogger->expects($this->at(0))->method('debug')
            ->with('$ out');
        $mockLogger->expects($this->at(1))->method('error')
            ->with('$ err');

        $shellCommandService->executeProcess($deployment, 'echo "out" ; echo "err" >&2 ', true, '$ ');
    }
}
