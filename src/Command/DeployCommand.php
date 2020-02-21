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
 * Surf deploy command
 */
class DeployCommand extends Command implements FactoryAwareInterface
{
    use FactoryAwareTrait;

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('deploy')
             ->setDescription('Deploys the application with the given name')
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
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configurationPath = $input->getOption('configurationPath');
        $deploymentName = $input->getArgument('deploymentName');
        $deployment = $this->factory->getDeployment($deploymentName, $configurationPath, false, true, $input->getOption('force'));
        $deployment->deploy();

        return $deployment->getStatus();
    }
}
