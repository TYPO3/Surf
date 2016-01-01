<?php
namespace TYPO3\Surf\Integration;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleLogger;
use TYPO3\Surf\Command\DeployCommand;
use TYPO3\Surf\Command\DescribeCommand;
use TYPO3\Surf\Command\ShowCommand;
use TYPO3\Surf\Command\SimulateCommand;
use TYPO3\Surf\Domain\Model\Deployment;

/**
 * Class Factory
 */
class Factory implements FactoryInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @return Command[]
     */
    public function createCommands()
    {
        return array(
            new ShowCommand(),
            new SimulateCommand(),
            new DescribeCommand(),
            new DeployCommand(),
        );
    }

    /**
     * @return ConsoleOutput
     */
    public function createOutput()
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
            $this->output->getFormatter()->setStyle('b', new OutputFormatterStyle(NULL, NULL, array('bold')));
            $this->output->getFormatter()->setStyle('i', new OutputFormatterStyle('black', 'white'));
            $this->output->getFormatter()->setStyle('u', new OutputFormatterStyle(NULL, NULL, array('underscore')));
            $this->output->getFormatter()->setStyle('em', new OutputFormatterStyle(NULL, NULL, array('reverse')));
            $this->output->getFormatter()->setStyle('strike', new OutputFormatterStyle(NULL, NULL, array('conceal')));
            $this->output->getFormatter()->setStyle('success', new OutputFormatterStyle('green'));
            $this->output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));
            $this->output->getFormatter()->setStyle('notice', new OutputFormatterStyle('yellow'));
            $this->output->getFormatter()->setStyle('info', new OutputFormatterStyle('white'));
        }

        return $this->output;
    }

    /**
     * @param string $deploymentName
     * @param string $configurationPath
     * @return Deployment
     */
    public function createDeployment($deploymentName, $configurationPath = null)
    {
        $deploymentService = new \TYPO3\Surf\Domain\Service\DeploymentService();
        $deployment = $deploymentService->getDeployment($deploymentName, $configurationPath);
        if ($deployment->getLogger() === null) {
            $logger = $this->createLogger();
            $deployment->setLogger($logger);
        }
        $deployment->initialize();

        return $deployment;
    }

    /**
     * @return ConsoleLogger
     */
    public function createLogger()
    {
        if ($this->logger === null) {
            $this->logger = new ConsoleLogger($this->createOutput());
        }
        return $this->logger;
    }

}