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
 * A base application with Git checkout and basic release directory structure.
 *
 * Most specific applications will extend from BaseApplication.
 */
class ConsoleApplication extends Application
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param InputInterface|null  $input
     * @param OutputInterface|null $output
     *
     * @throws \Exception
     *
     * @return int
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        foreach ($this->factory->createCommands() as $command) {
            $this->add($command);
        }

        return parent::run($input, $this->factory->createOutput());
    }

    /**
     * @param Command         $command
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        if ($command instanceof FactoryAwareInterface) {
            $command->setFactory($this->factory);
        }

        return parent::doRunCommand($command, $input, $output);
    }
}
