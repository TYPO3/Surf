<?php
namespace TYPO3\Surf\Command;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleLogger;

/**
 * AbstractSurfCommand
 */
class AbstractSurfCommand extends Command
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return \TYPO3\Surf\Domain\Model\Deployment
     */
    protected function createDeployment(InputInterface $input, OutputInterface $output)
    {
        $deploymentService = new \TYPO3\Surf\Domain\Service\DeploymentService();
        $configurationPath = $input->getOption('configurationPath');
        $deployment = $deploymentService->getDeployment($input->getArgument('deploymentName'), $configurationPath);
        if ($deployment->getLogger() === null) {
            $logger = $this->createDefaultLogger($output);
            $deployment->setLogger($logger);
        }
        $deployment->initialize();

        return $deployment;
    }

    /**
     * @param OutputInterface $output
     * @return ConsoleLogger
     */
    protected function createDefaultLogger(OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        return $logger;
    }


}
