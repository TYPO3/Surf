<?php
namespace TYPO3\Surf\Cli\Symfony;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Integration\FactoryAwareInterface;
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

    public function setFactory(FactoryInterface $factory): void
    {
        $this->factory = $factory;
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        foreach ($this->factory->createCommands() as $command) {
            $this->add($command);
        }
        return parent::run($input, $this->factory->createOutput());
    }

    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        if ($command instanceof FactoryAwareInterface) {
            $command->setFactory($this->factory);
        }
        return parent::doRunCommand($command, $input, $output);
    }
}
