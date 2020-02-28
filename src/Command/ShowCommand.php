<?php
namespace TYPO3\Surf\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Integration\FactoryAwareInterface;
use TYPO3\Surf\Integration\FactoryAwareTrait;

/**
 * Surf list command
 */
class ShowCommand extends Command implements FactoryAwareInterface
{
    use FactoryAwareTrait;

    protected function configure()
    {
        $this->setName('show')
            ->setDescription('Shows all the deployments depending on the directory configuration')
            ->addOption(
                'configurationPath',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path for deployment configuration files'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configurationPath = $input->getOption('configurationPath');
        $deploymentNames = $this->factory->getDeploymentNames($configurationPath);

        $output->writeln(sprintf(PHP_EOL . '<u>Deployments in "%s":</u>' . PHP_EOL, $this->factory->getDeploymentsBasePath($configurationPath)));
        foreach ($deploymentNames as $deploymentName) {
            $line = sprintf('  <info>%s</info>', $deploymentName);
            $output->writeln($line);
        }
        $output->writeln('');
    }
}
