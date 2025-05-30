<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Cli\Symfony;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Integration\FactoryInterface;

/**
 * @codeCoverageIgnore
 */
class ConsoleApplication extends Application
{
    protected FactoryInterface $factory;

    private OutputInterface $output;

    public function __construct(FactoryInterface $factory, OutputInterface $output, string $name, string $version)
    {
        parent::__construct($name, $version);
        $this->factory = $factory;
        $this->output = $output;
    }

    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        return parent::run($input, $this->output);
    }
}
