<?php
namespace TYPO3\Surf\Command;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Surf list command
 */
class ListCommand extends Command
{

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('surf:list')
            ->addOption(
                'configurationPath',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path for deployment configuration files'
            );
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configurationPath = $input->getOption('configurationPath');
        $deploymentService = new \TYPO3\Surf\Domain\Service\DeploymentService();
        $deploymentNames = $deploymentService->getDeploymentNames($configurationPath);

        $output->writeln('Deployments:' . PHP_EOL);

        foreach ($deploymentNames as $deploymentName) {
            $line = '  ' . $deploymentName;
            $output->writeln($line);
        }
    }
}
