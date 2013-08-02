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
class ShellCommandServiceTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * Test, if the given options are respected in executed SSH command
	 *
	 * @test
	 * @dataProvider commandOptionDataProvider
	 * @param string $expectedCommandArguments
	 * @param string $username
	 * @param string $password
	 * @param integer $port
	 */
	public function executeRemoteCommandRespectsOptionsInSshCommand($expectedCommandArguments, $username = NULL, $password = NULL, $port = NULL) {
		$service = $this->getAccessibleMock('TYPO3\Surf\Domain\Service\ShellCommandService', array('executeProcess'));

		$node = new \TYPO3\Surf\Domain\Model\Node('TestNode');
		$node->setHostname('remote-host.example.com');
		if ($username !== NULL) {
			$node->setOption('username', $username);
		}

		if ($password !== NULL) {
			$node->setOption('password', $password);

			$mockSurfPackage = $this->getMock('TYPO3\Flow\Package\PackageInterface');
			$mockSurfPackage->expects($this->once())->method('getResourcesPath')->will($this->returnValue('/your/path/to /TYPO3.Surf'));
			$mockPackageManager = $this->getMock('TYPO3\Flow\Package\PackageManagerInterface');
			$mockPackageManager->expects($this->once())->method('getPackage')->with('TYPO3.Surf')->will($this->returnValue($mockSurfPackage));
			$service->_set('packageManager', $mockPackageManager);
		}

		if ($port !== NULL) {
			$node->setOption('port', $port);
		}
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
		$mockLogger = $this->getMock('TYPO3\Flow\Log\LoggerInterface');
		$deployment->setLogger($mockLogger);

		$expectedCommand = $expectedCommandArguments .  ' \'echo "Hello World"\' 2>&1';
		$service->expects($this->once())->method('executeProcess')->with($this->anything(), $expectedCommand)->will($this->returnValue(array(0, 'Hello World')));

		$service->executeOrSimulate('echo "Hello World"', $node, $deployment);
	}

	/**
	 * Data provider for executeRemoteCommandRespectsOptionsInSshCommand
	 *
	 * @return array
	 */
	public function commandOptionDataProvider() {
		return array(
			array(
				'ssh -A \'remote-host.example.com\'',
				NULL,
				NULL,
				NULL
			),
			array(
				'ssh -A \'jdoe@remote-host.example.com\'',
				'jdoe',
				NULL,
				NULL
			),
			array(
				'ssh -A -p \'12345\' \'jdoe@remote-host.example.com\'',
				'jdoe',
				NULL,
				12345
			),

			array(
				'expect \'/your/path/to /TYPO3.Surf/Private/Scripts/PasswordSshLogin.expect\' \'myPassword\' ssh -A -o PubkeyAuthentication=no \'jdoe@remote-host.example.com\'',
				'jdoe',
				'myPassword',
				NULL
			),
		);
	}

	/**
	 * @test
	 */
	public function executeRemoteCommandRespectsRemoteCommandExecutionHandler() {
		$shellCommandService = new \TYPO3\Surf\Domain\Service\ShellCommandService();

		$node = new \TYPO3\Surf\Domain\Model\Node('TestNode');
		$node->setHostname('asdf');
		$arguments = array();

		$node->setOption('remoteCommandExecutionHandler', function(\TYPO3\Surf\Domain\Service\ShellCommandService $shellCommandService, $command, \TYPO3\Surf\Domain\Model\Node $node, \TYPO3\Surf\Domain\Model\Deployment $deployment, $logOutput) use(&$arguments) {
			$arguments = func_get_args();
			return array(0, 'Hello World');
		});

		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
		$mockLogger = $this->getMock('TYPO3\Flow\Log\LoggerInterface');
		$deployment->setLogger($mockLogger);

		$response = $shellCommandService->execute('foo command', $node, $deployment);
		$this->assertEquals('Hello World', $response);
		$this->assertSame(array(
			$shellCommandService,
			'foo command',
			$node,
			$deployment,
			TRUE
		), $arguments);
	}

	/**
	 * @test
	 */
	public function executeOnRemoteNodeJoinsCommandsWithAndOperator() {
		$shellCommandService = $this->getMock('TYPO3\Surf\Domain\Service\ShellCommandService', array('executeProcess'));

		$node = new \TYPO3\Surf\Domain\Model\Node('TestNode');
		$node->setHostname('asdf');

		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
		$mockLogger = $this->getMock('TYPO3\Flow\Log\LoggerInterface');
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
	public function executeOnLocalNodeJoinsCommandsWithAndOperator() {
		$shellCommandService = $this->getMock('TYPO3\Surf\Domain\Service\ShellCommandService', array('executeProcess'));

		$node = new \TYPO3\Surf\Domain\Model\Node('TestNode');
		$node->setHostname('localhost');

		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
		$mockLogger = $this->getMock('TYPO3\Flow\Log\LoggerInterface');
		$deployment->setLogger($mockLogger);

		$shellCommandService->expects($this->any())->method('executeProcess')->with(
			$deployment, $this->stringContains('bin/false && ls -al')
		)->will($this->returnValue(array(0, 'Foo')));

		$response = $shellCommandService->execute(array('bin/false', 'ls -al'), $node, $deployment);

		$this->assertEquals('Foo', $response);
	}

}
?>
