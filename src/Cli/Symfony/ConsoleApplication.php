<?php
namespace TYPO3\Surf\Cli\Symfony;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Integration\FactoryInterface;

/**
 * @codeCoverageIgnore
 */
class ConsoleApplication extends Application
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(FactoryInterface $factory, OutputInterface $output, string $name = 'TYPO3 Surf', string $version = '3.0.0-alpha')
    {
        parent::__construct($name, $version);
        $this->factory = $factory;
        $this->output = $output;
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        return parent::run($input, $this->output);
    }
}
