<?php

declare(strict_types=1);

namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\MockObject\MockObject;
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
    public function executeRemoteCommandRespectsOptionsInSshCommand(
        $expectedCommandArguments,
        $username = null,
        $password = null,
        $port = null,
        $privateKey = null
    ): void {
        /** @var MockObject|ShellCommandService $service */
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

        /** @var LoggerInterface|MockObject $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);
        $deployment->setLogger($mockLogger);

        $expectedCommand = $expectedCommandArguments . ' \'echo "Hello World"\'';
        $service
            ->expects(self::once())
            ->method('executeProcess')
            ->with(self::anything(), $expectedCommand)
            ->will(self::returnValue([0, 'Hello World']));

        $service->executeOrSimulate('echo "Hello World"', $node, $deployment);
    }

    /**
     * Data provider for executeRemoteCommandRespectsOptionsInSshCommand
     *
     * @return array
     */
    public function commandOptionDataProvider(): array
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
    public function executeRemoteCommandRespectsRemoteCommandExecutionHandler(): void
    {
        $shellCommandService = new ShellCommandService();

        $node = new Node('TestNode');
        $node->setHostname('asdf');
        $arguments = [];

        $node->setRemoteCommandExecutionHandler(function (ShellCommandService $shellCommandService, $command, Node $node, Deployment $deployment, $logOutput) use (&$arguments): array {
            $arguments = func_get_args();
            return [0, 'Hello World'];
        });

        $deployment = new Deployment('TestDeployment');

        /** @var LoggerInterface|MockObject $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);
        $deployment->setLogger($mockLogger);

        $response = $shellCommandService->execute('foo command', $node, $deployment);

        self::assertSame('Hello World', $response);
        self::assertSame([
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
    public function executeOnRemoteNodeJoinsCommandsWithAndOperator(): void
    {
        /** @var MockObject|ShellCommandService $shellCommandService */
        $shellCommandService = $this->createPartialMock(ShellCommandService::class, ['executeProcess']);

        $node = new Node('TestNode');
        $node->setHostname('asdf');

        $deployment = new Deployment('TestDeployment');

        /** @var LoggerInterface|MockObject $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);
        $deployment->setLogger($mockLogger);

        $shellCommandService
            ->expects(self::any())
            ->method('executeProcess')
            ->with(
                $deployment,
                $this->stringContains('bin/false && ls -al')
            )
            ->will(self::returnValue([0, 'Foo']));

        $response = $shellCommandService->execute(['bin/false', 'ls -al'], $node, $deployment);

        self::assertSame('Foo', $response);
    }

    /**
     * @test
     */
    public function executeOnLocalNodeJoinsCommandsWithAndOperator(): void
    {
        /** @var MockObject|ShellCommandService $shellCommandService */
        $shellCommandService = $this->createPartialMock(ShellCommandService::class, ['executeProcess']);

        $node = new Node('TestNode');
        $node->onLocalhost();

        $deployment = new Deployment('TestDeployment');

        /** @var LoggerInterface|MockObject $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);
        $deployment->setLogger($mockLogger);

        $shellCommandService
            ->expects(self::any())
            ->method('executeProcess')
            ->with(
                $deployment,
                $this->stringContains('bin/false && ls -al')
            )
            ->will(self::returnValue([0, 'Foo']));

        $response = $shellCommandService->execute(['bin/false', 'ls -al'], $node, $deployment);

        self::assertSame('Foo', $response);
    }

    /**
     * @test
     */
    public function executeProcessProperlyLogsStandardAndErrorOutput(): void
    {
        $shellCommandService = new ShellCommandService();
        $deployment = new Deployment('TestDeployment');
        /** @var LoggerInterface|MockObject $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);
        $deployment->setLogger($mockLogger);

        $mockLogger->expects(self::at(0))->method('debug')->with('$ out');
        $mockLogger->expects(self::at(1))->method('error')->with('$ err');

        $shellCommandService->executeProcess($deployment, 'echo "out" ; echo "err" >&2 ', true, '$ ');
    }
}
