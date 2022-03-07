<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Integration\FactoryInterface;

class ShowCommand extends Command
{
    private FactoryInterface $factory;

    /**
     * @var string
     */
    protected static $defaultName = 'show';

    public function __construct(FactoryInterface $factory)
    {
        parent::__construct();
        $this->factory = $factory;
    }

    protected function configure(): void
    {
        $this->setDescription('Shows all the deployments depending on the directory configuration')
            ->addOption(
                'configurationPath',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path for deployment configuration files'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configurationPath = $input->getOption('configurationPath');
        $deploymentNames = $this->factory->getDeploymentNames($configurationPath);

        $output->writeln(sprintf(PHP_EOL . '<u>Deployments in "%s":</u>' . PHP_EOL, $this->factory->getDeploymentsBasePath($configurationPath)));
        foreach ($deploymentNames as $deploymentName) {
            $line = sprintf('  <info>%s</info>', $deploymentName);
            $output->writeln($line);
        }
        $output->writeln('');

        return Command::SUCCESS;
    }
}
