<?php
declare(strict_types = 1);

namespace TYPO3\Surf\Cli\Symfony;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleOutputFactory
{
    public function createOutput(): OutputInterface
    {
        $output = new ConsoleOutput();
        $output->getFormatter()->setStyle('b', new OutputFormatterStyle(null, null, ['bold']));
        $output->getFormatter()->setStyle('i', new OutputFormatterStyle('black', 'white'));
        $output->getFormatter()->setStyle('u', new OutputFormatterStyle(null, null, ['underscore']));
        $output->getFormatter()->setStyle('em', new OutputFormatterStyle(null, null, ['reverse']));
        $output->getFormatter()->setStyle('strike', new OutputFormatterStyle(null, null, ['conceal']));
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));
        $output->getFormatter()->setStyle('notice', new OutputFormatterStyle('yellow'));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('white', null, ['bold']));
        $output->getFormatter()->setStyle('debug', new OutputFormatterStyle('white'));

        return $output;
    }
}
