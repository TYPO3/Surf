<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
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
class ShellCommandServiceTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * Test, if the given options are respected in executed SSH command
	 *
	 * @test
	 * @dataProvider commandOptionDataProvider
	 * @param string $expectedCommandArguments
	 * @param string $username
	 * @param int $port
	 */
	public function executeRemoteCommandRespectsOptionsInSshCommand($expectedCommandArguments, $username = NULL, $port = NULL) {
		$node = new \TYPO3\Surf\Domain\Model\Node('TestNode');
		$node->setHostname('remote-host.example.com');
		if ($username !== NULL) {
			$node->setOption('username', $username);
		}
		if ($port !== NULL) {
			$node->setOption('port', $port);
		}
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('TestDeployment');
		$mockLogger = $this->getMock('TYPO3\FLOW3\Log\LoggerInterface');
		$deployment->setLogger($mockLogger);

		$service = $this->getMock('TYPO3\Surf\Domain\Service\ShellCommandService', array('executeProcess'));
		$expectedCommand = 'ssh -A ' . $expectedCommandArguments .  ' \'echo "Hello World"\' 2>&1';
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
				'\'remote-host.example.com\'',
				NULL,
				NULL
			),
			array(
				'\'jdoe@remote-host.example.com\'',
				'jdoe',
				NULL
			),
			array(
				'-p \'12345\' \'jdoe@remote-host.example.com\'',
				'jdoe',
				12345
			),
		);
	}

}
?>