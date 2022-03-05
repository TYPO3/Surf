<?php

declare(strict_types=1);

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

class DeployCommand extends Command
{
    private FactoryInterface $factory;

    /**
     * @var string
     */
    protected static $defaultName = 'deploy';

    public function __construct(FactoryInterface $factory)
    {
        parent::__construct();
        $this->factory = $factory;
    }

    protected function configure(): void
    {
        $this->setDescription('Deploys the application with the given name')
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
        $configurationPath = $input->getOption('configurationPath');
        $deploymentName = $input->getArgument('deploymentName');
        $deployment = $this->factory->getDeployment((string)$deploymentName, $configurationPath, false, true, $input->getOption('force'));
        $deployment->deploy();

        return $deployment->getStatus()->toInt();
    }
}
