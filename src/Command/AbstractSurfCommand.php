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
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleLogger;

/**
 * AbstractSurfCommand
 */
class AbstractSurfCommand extends Command
{
    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deployment = $this->createDeployment($input, $output);

        $deployment->deploy();
    }

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
