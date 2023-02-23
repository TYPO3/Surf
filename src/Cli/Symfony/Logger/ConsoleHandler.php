<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Cli\Symfony\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Writes logs to the console output depending on its verbosity setting.
 *
 * The minimum logging level at which this handler will be triggered depends on the
 * verbosity setting of the console output. The default mapping is:
 * - OutputInterface::VERBOSITY_NORMAL will show all WARNING and higher logs
 * - OutputInterface::VERBOSITY_VERBOSE (-v) will show all NOTICE and higher logs
 * - OutputInterface::VERBOSITY_VERY_VERBOSE (-vv) will show all INFO and higher logs
 * - OutputInterface::VERBOSITY_DEBUG (-vvv) will show all DEBUG and higher logs, i.e. all logs
 *
 * This mapping can be customized with the $verbosityLevelMap constructor parameter.
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Monolog/Handler/ConsoleHandler.php
 */
class ConsoleHandler extends AbstractProcessingHandler
{
    private OutputInterface $output;

    private array $verbosityLevelMap = [
        OutputInterface::VERBOSITY_NORMAL => Logger::INFO,
        OutputInterface::VERBOSITY_VERBOSE => Logger::DEBUG,
        OutputInterface::VERBOSITY_VERY_VERBOSE => Logger::DEBUG,
        OutputInterface::VERBOSITY_DEBUG => Logger::DEBUG,
    ];

    public function __construct(OutputInterface $output, bool $bubble = true, array $verbosityLevelMap = [])
    {
        parent::__construct(Logger::DEBUG, $bubble);
        $this->output = $output;

        if ($verbosityLevelMap !== []) {
            $this->verbosityLevelMap = $verbosityLevelMap;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record): bool
    {
        if (!$this->updateLevel()) {
            return false;
        }
        return parent::isHandling($record);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record): bool
    {
        // we have to update the logging level each time because the verbosity of the
        // console output might have changed in the meantime (it is not immutable)
        if (!$this->updateLevel()) {
            return false;
        }
        return parent::handle($record);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        $this->output->write((string)$record['formatted']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormatter(): ConsoleFormatter
    {
        return new ConsoleFormatter();
    }

    /**
     * Updates the logging level based on the verbosity setting of the console output.
     */
    private function updateLevel(): bool
    {
        if (OutputInterface::VERBOSITY_QUIET === $verbosity = $this->output->getVerbosity()) {
            return false;
        }

        if (isset($this->verbosityLevelMap[$verbosity])) {
            $this->setLevel($this->verbosityLevelMap[$verbosity]);
        } else {
            $this->setLevel(Logger::DEBUG);
        }

        return true;
    }
}
