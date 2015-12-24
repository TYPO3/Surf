<?php
namespace TYPO3\Surf\Log\Backend;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use \TYPO3\Surf\Helper\Ansi;

/**
 * Extended ANSI console backend with human friendly formatting
 */
class AnsiConsoleBackend extends \TYPO3\Flow\Log\Backend\ConsoleBackend {

	/**
	 * @var array
	 */
	protected $tagFormats = array();

	/**
	 * @var boolean
	 */
	protected $disableAnsi = FALSE;

	/**
	 * Open the log backend
	 *
	 * Initializes tag formats.
	 *
	 * @return void
	 */
	public function open() {
		parent::open();
		$this->tagFormats = array(
			'success' => Ansi::FG_GREEN . '|' . Ansi::END,
			'info' => Ansi::FG_WHITE . '|' . Ansi::END,
			'notice' => Ansi::FG_YELLOW . '|' . Ansi::END,
			'debug' => Ansi::FG_GRAY . '|' . Ansi::END,
			'error' => Ansi::FG_WHITE . Ansi::BG_RED . '|' . Ansi::END,
			'warning' => Ansi::FG_BLACK . Ansi::BG_YELLOW . '|' . Ansi::END
		);
	}

	/**
	 * @param string $message
	 * @param int $severity
	 * @param array $additionalData
	 * @param string $packageKey
	 * @param string $className
	 * @param string $methodName
	 * @return void
	 */
	public function append($message, $severity = LOG_INFO, $additionalData = NULL, $packageKey = NULL, $className = NULL, $methodName = NULL) {
		if ($severity > $this->severityThreshold) {
			return;
		}

		$severityName = strtolower(trim($this->severityLabels[$severity]));
		$output = '<' . $severityName. '>' . $message . '</' . $severityName . '>';

		$output = $this->formatOutput($output);

		if (is_resource($this->streamHandle)) {
			fputs($this->streamHandle, $output . PHP_EOL);
		}
	}

	/**
	 * Apply ansi formatting to output according to tags
	 *
	 * @param string $output
	 * @return string
	 */
	protected function formatOutput($output) {
		$tagFormats = $this->tagFormats;
		$disableAnsi = $this->disableAnsi;
		do {
			$lastOutput = $output;
			$output = preg_replace_callback('|(<([^>]+?)>(.*?)</\2>)|s', function($matches) use ($tagFormats, $disableAnsi) {
				$format = isset($tagFormats[$matches[2]]) ? $tagFormats[$matches[2]] : '|';
				if ($disableAnsi) {
					return $matches[3];
				} else {
					return str_replace('|', $matches[3], $format);
				}
			}, $output);
		} while ($lastOutput !== $output);
		return $output;
	}

	/**
	 * @param boolean $disableAnsi
	 */
	public function setDisableAnsi($disableAnsi) {
		$this->disableAnsi = $disableAnsi;
	}

	/**
	 * @return boolean
	 */
	public function getDisableAnsi() {
		return $this->disableAnsi;
	}

}
?>