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
use TYPO3\Surf\Integration\FactoryAwareInterface;
use TYPO3\Surf\Integration\FactoryAwareTrait;

/**
 * Surf simulate command
 */
class SimulateCommand extends Command implements FactoryAwareInterface
{
    use FactoryAwareTrait;

    protected function configure()
    {
        $this->setName('simulate')
             ->setDescription('Simulates the deployment for the given name')
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

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configurationPath = $input->getOption('configurationPath');
        $deploymentName = $input->getArgument('deploymentName');
        $deployment = $this->factory->getDeployment($deploymentName, $configurationPath, true, true, $input->getOption('force'));
        $deployment->simulate();

        return $deployment->getStatus();
    }
}
