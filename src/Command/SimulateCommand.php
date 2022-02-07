<?php

namespace TYPO3\Surf\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Integration\FactoryInterface;

class SimulateCommand extends Command
{
    private FactoryInterface $factory;

    /**
     * @var string
     */
    protected static $defaultName = 'simulate';

    public function __construct(FactoryInterface $factory)
    {
        parent::__construct();
        $this->factory = $factory;
    }

    protected function configure(): void
    {
        $this->setDescription('Simulates the deployment for the given name')
             ->addArgument(
                 'deploymentName',
                 InputArgument::OPTIONAL,
                 'The deployment name'
             )
             ->addOption(
                 'configurationPath',
                 null,
                 InputOption::VALUE_OPTIONAL,
                 'Path for deployment configuration files'
             )
             ->addOption(
                 'force',
                 null,
                 InputOption::VALUE_NONE,
                 'Force deployment will execute unlock task in simple workflow'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configurationPath = (string)$input->getOption('configurationPath');
        $deploymentName = (string)$input->getArgument('deploymentName');
        $force = (bool)$input->getOption('force');

        $deployment = $this->factory->getDeployment($deploymentName, $configurationPath, true, true, $force);
        $deployment->simulate();

        return $deployment->getStatus();
    }
}
