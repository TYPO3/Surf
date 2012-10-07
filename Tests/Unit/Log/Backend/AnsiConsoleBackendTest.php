<?php
namespace TYPO3\Surf\Tests\Unit\Log\Backend;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use \TYPO3\Surf\Helper\Ansi;
use org\bovigo\vfs\vfsStream;

/**
 * Unit tests for AnsiConsoleBackend
 */
class AnsiConsoleBackendTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @return array
	 */
	public function logMessages() {
		return array(
			array('My info message', LOG_INFO, Ansi::FG_WHITE . 'My info message' . Ansi::END),
			array('My error message', LOG_ERR, Ansi::FG_WHITE . Ansi::BG_RED . 'My error message' . Ansi::END),
			array('Notice with <success>success</success> message', LOG_NOTICE, Ansi::FG_YELLOW . 'Notice with ' . Ansi::FG_GREEN . 'success' . Ansi::END . ' message' . Ansi::END)
		);
	}

	/**
	 * @test
	 * @dataProvider logMessages
	 */
	public function appendWillWrapMessageWithFormatTag($message, $severity, $expectedOutput) {
		$backend = new \TYPO3\Surf\Log\Backend\AnsiConsoleBackend();
		$backend->open();

		\org\bovigo\vfs\vfsStreamWrapper::register();
		\org\bovigo\vfs\vfsStreamWrapper::setRoot(new \org\bovigo\vfs\vfsStreamDirectory('Foo'));

		$streamHandle = fopen('vfs://Foo/log', 'w');
		$this->inject($backend, 'streamHandle', $streamHandle);

		$backend->append($message, $severity);

		$result = file_get_contents('vfs://Foo/log');
		$this->assertEquals($expectedOutput . PHP_EOL, $result);
	}

}
?>