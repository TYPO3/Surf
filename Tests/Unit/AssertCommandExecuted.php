<?php


namespace TYPO3\Surf\Tests\Unit;


class AssertCommandExecuted extends \PHPUnit_Framework_Constraint {

	/**
	 * @var string
	 */
	protected $expectedCommand;

	/**
	 * @param string $expectedCommand The expected, executed command substring
	 */
	public function __construct($expectedCommand) {
		if (!is_string($expectedCommand)) {
			throw new \InvalidArgumentException('Expected command should be a string, ' .  gettype($expectedCommand) . ' given');
		}
		$this->expectedCommand = $expectedCommand;
		parent::__construct();
	}

	/**
	 * Evaluates the constraint for parameter $other. Returns true if the
	 * constraint is met, false otherwise.
	 *
	 * This method can be overridden to implement the evaluation algorithm.
	 *
	 * @param  array $other array to evaluate.
	 * @return boolean
	 */
	protected function matches($other) {
		if (!is_array($other)) {
			throw new \InvalidArgumentException('Expected an array of executed commands as value, ' . gettype($other) . ' given');
		}

		foreach ($other as $command) {
			if (strpos($this->expectedCommand, '/') === 0) {
				if (preg_match($this->expectedCommand, $command)) {
					return TRUE;
				}
			} else {
				if (strpos($command, $this->expectedCommand) !== FALSE) {
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toString() {
		return 'contains the command substring [' . $this->expectedCommand . ']';
	}

}