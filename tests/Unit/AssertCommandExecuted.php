<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Constraint\Constraint;

class AssertCommandExecuted extends Constraint
{
    protected string $expectedCommand;

    public function __construct(string $expectedCommand)
    {
        $this->expectedCommand = $expectedCommand;
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
    protected function matches($other): bool
    {
        if (!is_array($other)) {
            throw new InvalidArgumentException('Expected an array of executed commands as value, ' . gettype($other) . ' given');
        }

        foreach ($other as $command) {
            if (strpos($this->expectedCommand, '/') === 0) {
                if (preg_match($this->expectedCommand, $command)) {
                    return true;
                }
            } elseif (strpos($command, $this->expectedCommand) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'contains the command substring [' . $this->expectedCommand . ']';
    }
}
