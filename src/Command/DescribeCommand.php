<?php
namespace TYPO3\Surf\Command;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Integration\FactoryAwareInterface;
use TYPO3\Surf\Integration\FactoryAwareTrait;

/**
 * Surf describe command
 */
class DescribeCommand extends Command implements FactoryAwareInterface
{
    use FactoryAwareTrait;

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
        $deploymentName = $input->getArgument('deploymentName');
        $deployment = $this->factory->getDeployment($deploymentName, $configurationPath);

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
