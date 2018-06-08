<?php
namespace TYPO3\Surf\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

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
        $this->setName('please');
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->executeProcess('git clean -dffx');
        $this->executeProcess('composer install --no-ansi --no-interaction --no-dev --no-progress --classmap-authoritative');
        $this->executeProcess('phar-composer build . ../surf.phar');

        // disabled for the time being
//        $this->signPhar();
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
        return [$exitCode, trim($process->getOutput())];
    }

    protected function signPhar()
    {
        $question = (new Question('Pass phrase for private key: '))
            ->setHidden(true)
            ->setHiddenFallback(true);
        $questionHelper = new QuestionHelper();
        $passPhrase = $questionHelper->ask($this->input, $this->output, $question);

        $privateKeyResource = openssl_pkey_get_private('file://~/.openssl/surf.private.pem', $passPhrase);
        openssl_pkey_export($privateKeyResource, $exportedPrivateKey);

        $phar = new \Phar('../surf.phar');
        $phar->setSignatureAlgorithm(\Phar::OPENSSL, $exportedPrivateKey);
    }
}
