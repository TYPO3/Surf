<?php
namespace TYPO3\Surf\Integration;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Domain\Model\Deployment;

interface FactoryInterface
{
    /**
     * @return Command[]
     */
    public function createCommands();

    /**
     * @return OutputInterface
     */
    public function createOutput();

    /**
     * @param string $deploymentName
     * @param string|null $configurationPath
     * @param bool $simulateDeployment
     * @return Deployment
     */
    public function createDeployment($deploymentName, $configurationPath = null, $simulateDeployment = true);

    /**
     * @return LoggerInterface
     */
    public function createLogger();

}