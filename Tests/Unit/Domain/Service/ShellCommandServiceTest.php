<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Service\ShellCommandService;

/**
 * Unit test for the ShellCommandService
 */
class ShellCommandServiceTest extends TestCase
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
     * @param string $privateKey
     */
    public function executeRemoteCommandRespectsOptionsInSshCommand($expectedCommandArguments, $username = null, $password = null, $port = null, $privateKey = null)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ShellCommandService $service */
        $service = $this->createPartialMock(ShellCommandService::class, ['executeProcess']);

        $node = new Node('TestNode');
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

        if ($privateKey !== null) {
            $node->setOption('privateKeyFile', $privateKey);
        }

        $deployment = new Deployment('TestDeployment');
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);
        $deployment->setLogger($mockLogger);

        $expectedCommand = $expectedCommandArguments . ' \'echo "Hello World"\'';
        $service->expects($this->once())->method('executeProcess')->with($this->anything(), $expectedCommand)->will($this->returnValue([0, 'Hello World']));

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
        return [
            [
                'ssh -A \'remote-host.example.com\'',
                null,
                null,
                null
            ],
            [
                'ssh -A \'jdoe@remote-host.example.com\'',
                'jdoe',
                null,
                null
            ],
            [
                'ssh -A -p \'12345\' \'jdoe@remote-host.example.com\'',
                'jdoe',
                null,
                12345
            ],
            [
                'ssh -A -i \'~/.ssh/foo\' \'jdoe@remote-host.example.com\'',
                'jdoe',
                null,
                null,
                '~/.ssh/foo'
            ],
            [
                'expect \'' . $resourcesPath . '/Private/Scripts/PasswordSshLogin.expect\' \'myPassword\' ssh -A -o PubkeyAuthentication=no \'jdoe@remote-host.example.com\'',
                'jdoe',
                'myPassword',
                null
            ],
        ];
    }

    /**
     * @test
     */
    public function executeRemoteCommandRespectsRemoteCommandExecutionHandler()
    {
        $shellCommandService = new ShellCommandService();

        $node = new Node('TestNode');
        $node->setHostname('asdf');
        $arguments = [];

        $node->setOption('remoteCommandExecutionHandler', function (ShellCommandService $shellCommandService, $command, Node $node, Deployment $deployment, $logOutput) use (&$arguments) {
            $arguments = func_get_args();
            return [0, 'Hello World'];
        });

        $deployment = new Deployment('TestDeployment');
        $mockLogger = $this->createMock(LoggerInterface::class);
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $deployment->setLogger($mockLogger);

        $response = $shellCommandService->execute('foo command', $node, $deployment);
        $this->assertEquals('Hello World', $response);
        $this->assertSame([
            $shellCommandService,
            'foo command',
            $node,
            $deployment,
            true
        ], $arguments);
    }

    /**
     * @test
     */
    public function executeOnRemoteNodeJoinsCommandsWithAndOperator()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ShellCommandService $shellCommandService */
        $shellCommandService = $this->createPartialMock(ShellCommandService::class, ['executeProcess']);

        $node = new Node('TestNode');
        $node->setHostname('asdf');

        $deployment = new Deployment('TestDeployment');
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);
        $deployment->setLogger($mockLogger);

        $shellCommandService->expects($this->any())->method('executeProcess')->with(
            $deployment,
            $this->stringContains('bin/false && ls -al')
        )->will($this->returnValue([0, 'Foo']));

        $response = $shellCommandService->execute(['bin/false', 'ls -al'], $node, $deployment);

        $this->assertEquals('Foo', $response);
    }

    /**
     * @test
     */
    public function executeOnLocalNodeJoinsCommandsWithAndOperator()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ShellCommandService $shellCommandService */
        $shellCommandService = $this->createPartialMock(ShellCommandService::class, ['executeProcess']);

        $node = new Node('TestNode');
        $node->setHostname('localhost');

        $deployment = new Deployment('TestDeployment');
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);
        $deployment->setLogger($mockLogger);

        $shellCommandService->expects($this->any())->method('executeProcess')->with(
            $deployment,
            $this->stringContains('bin/false && ls -al')
        )->will($this->returnValue([0, 'Foo']));

        $response = $shellCommandService->execute(['bin/false', 'ls -al'], $node, $deployment);

        $this->assertEquals('Foo', $response);
    }

    /**
     * @test
     */
    public function executeProcessProperlyLogsStandardAndErrorOutput()
    {
        $shellCommandService = new ShellCommandService();
        $deployment = new Deployment('TestDeployment');
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);
        $deployment->setLogger($mockLogger);

        $mockLogger->expects($this->at(0))->method('debug')
            ->with('$ out');
        $mockLogger->expects($this->at(1))->method('error')
            ->with('$ err');

        $shellCommandService->executeProcess($deployment, 'echo "out" ; echo "err" >&2 ', true, '$ ');
    }
}
