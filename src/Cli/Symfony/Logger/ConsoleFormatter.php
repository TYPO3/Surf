<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Cli\Symfony\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

/**
 * Formats incoming records for console output by coloring them depending on log level.
 * Outputs the message only.
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Monolog/Formatter/ConsoleFormatter.php
 */
class ConsoleFormatter extends LineFormatter
{
    public const SIMPLE_FORMAT = "%start_tag%%message%%end_tag%\n";

    /**
     * {@inheritdoc}
     */
    public function format(array $record): string
    {
        if ($record['level'] >= Logger::ERROR) {
            $record['start_tag'] = '<error>';
            $record['end_tag'] = '</error>';
        } elseif ($record['level'] >= Logger::NOTICE) {
            $record['start_tag'] = '<comment>';
            $record['end_tag'] = '</comment>';
        } elseif ($record['level'] >= Logger::INFO) {
            $record['start_tag'] = '<info>';
            $record['end_tag'] = '</info>';
        } else {
            $record['start_tag'] = '<debug>';
            $record['end_tag'] = '</debug>';
        }

        return parent::format($record);
    }
}
