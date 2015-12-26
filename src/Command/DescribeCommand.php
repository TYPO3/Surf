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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Surf describe command
 */
class DescribeCommand extends Command
{

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('describe')
            ->addArgument(
                'deploymentName',
                InputArgument::REQUIRED,
                'The deployment name'
            )
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
        $deployment = $deploymentService->getDeployment($input->getArgument('deploymentName'), $configurationPath);

        $deployment->initialize();

        $output->writeln('Deployment' . $deployment->getName());
        $output->writeln('');
        $output->writeln('Workflow: ' . $deployment->getWorkflow()->getName() . PHP_EOL);
        $output->writeln('Nodes:' . PHP_EOL);
        foreach ($deployment->getNodes() as $node) {
            $output->writeln('  ' . $node->getName() . ' (' . $node->getHostname() . ')');
        }
        $output->writeln(PHP_EOL . 'Applications:' . PHP_EOL);
        foreach ($deployment->getApplications() as $application) {
            $output->writeln(' ' . $application->getName() . PHP_EOL);
            $output->writeln('    Deployment path: ' . $application->getDeploymentPath());
            $output->writeln('    Options: ');
            foreach ($application->getOptions() as $key => $value) {
                $output->writeln('      ' . $key . ' => ' . $value);
            }
            $output->writeln('    Nodes: ' . implode(', ', $application->getNodes()));
        }
    }
}
