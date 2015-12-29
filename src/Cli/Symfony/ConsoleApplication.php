<?php
namespace TYPO3\Surf\Cli\Symfony;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Command\DeployCommand;
use TYPO3\Surf\Command\DescribeCommand;
use TYPO3\Surf\Command\ShowCommand;
use TYPO3\Surf\Command\SimulateCommand;

/**
 * A base application with Git checkout and basic release directory structure
 *
 * Most specific applications will extend from BaseApplication.
 */
class ConsoleApplication extends \Symfony\Component\Console\Application
{
    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     * @throws \Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->add(new ShowCommand());
        $this->add(new SimulateCommand());
        $this->add(new DescribeCommand());
        $this->add(new DeployCommand());
        
        if ($output === null) {
            $output = new ConsoleOutput();
            $output->getFormatter()->setStyle('b', new OutputFormatterStyle(NULL, NULL, array('bold')));
            $output->getFormatter()->setStyle('i', new OutputFormatterStyle('black', 'white'));
            $output->getFormatter()->setStyle('u', new OutputFormatterStyle(NULL, NULL, array('underscore')));
            $output->getFormatter()->setStyle('em', new OutputFormatterStyle(NULL, NULL, array('reverse')));
            $output->getFormatter()->setStyle('strike', new OutputFormatterStyle(NULL, NULL, array('conceal')));
            $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green'));
            $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));
            $output->getFormatter()->setStyle('notice', new OutputFormatterStyle('yellow'));
            $output->getFormatter()->setStyle('info', new OutputFormatterStyle('white'));
        }
        return parent::run($input, $output);
    }

}