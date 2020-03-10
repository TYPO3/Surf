<?php
declare(strict_types = 1);

namespace TYPO3\Surf\Cli\Symfony\Logger;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class LoggerFactory
{
    /**
     * @var ConsoleHandler
     */
    private $consoleHandler;

    public function __construct(ConsoleHandler $consoleHandler)
    {
        $this->consoleHandler = $consoleHandler;
    }

    public function createLogger(): LoggerInterface
    {
        return new Logger('TYPO3 Surf', [$this->consoleHandler]);
    }
}
