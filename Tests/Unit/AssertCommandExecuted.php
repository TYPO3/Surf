<?php
namespace TYPO3\Surf\Tests\Unit;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use InvalidArgumentException;
use PHPUnit\Framework\Constraint\Constraint;

if (!class_exists(Constraint::class)) {
    class_alias('PHPUnit_Framework_Constraint', Constraint::class);
}
/**
 * Class AssertCommandExecuted
 */
class AssertCommandExecuted extends Constraint
{
    /**
     * @var string
     */
    protected $expectedCommand;

    /**
     * @param string $expectedCommand The expected, executed command substring
     */
    public function __construct($expectedCommand)
    {
        if (!is_string($expectedCommand)) {
            throw new InvalidArgumentException('Expected command should be a string, ' . gettype($expectedCommand) . ' given');
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
     * @return bool
     */
    protected function matches($other)
    {
        if (!is_array($other)) {
            throw new \InvalidArgumentException('Expected an array of executed commands as value, ' . gettype($other) . ' given');
        }

        foreach ($other as $command) {
            if (strpos($this->expectedCommand, '/') === 0) {
                if (preg_match($this->expectedCommand, $command)) {
                    return true;
                }
            } else {
                if (strpos($command, $this->expectedCommand) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'contains the command substring [' . $this->expectedCommand . ']';
    }
}
