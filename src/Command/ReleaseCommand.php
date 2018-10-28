<?php
namespace TYPO3\Surf\Command;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

/**
 * Surf release command
 */
class ReleaseCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('release');
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->executeProcess('composer install --no-ansi --no-interaction --no-dev --no-progress --classmap-authoritative');
        $this->executeProcess('box build');
    }

    /**
     * Open a process with symfony/process and process each line by logging and
     * collecting its output.
     *
     * @param string $command
     * @return array The exit code of the command and the returned output
     */
    public function executeProcess($command)
    {
        $process = new Process($command);
        $process->setTimeout(null);
        $output = $this->output;
        $callback = function ($type, $data) use ($output) {
            $output->writeln(trim($data));
        };
        $exitCode = $process->run($callback);
        return array($exitCode, trim($process->getOutput()));
    }

}
