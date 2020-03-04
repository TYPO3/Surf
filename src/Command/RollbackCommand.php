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

class RollbackCommand extends Command implements FactoryAwareInterface
{
    use FactoryAwareTrait;

    protected function configure()
    {
        $this->setName('rollback')
            ->setDescription('Rollback current to previous release and remove current folder')
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
            )->addOption(
                'simulate',
                null,
                InputOption::VALUE_NONE,
                'Simulate rollback'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null
     * @throws \TYPO3\Surf\Exception
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configurationPath = $input->getOption('configurationPath');
        $deploymentName = $input->getArgument('deploymentName');
        $deployment = $this->factory->getDeployment($deploymentName, $configurationPath, $input->getOption('simulate'), false);
        $deployment->rollback($input->getOption('simulate'));

        return $deployment->getStatus();
    }
}
