<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Service;

use Monolog\Logger;
use Neos\Utility\Files;
use Phar;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\Process;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * A shell command service
 */
class ShellCommandService implements LoggerAwareInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    use LoggerAwareTrait;

    /**
     * Execute a shell command (locally or remote depending on the node hostname)
     *
     * @param array|string $command The shell command to execute, either string or array of commands
     * @param bool $ignoreErrors If this command should ignore exit codes unequal zero
     * @param bool $logOutput TRUE if the output of the command should be logged
     * @return mixed The output of the shell command or false if the command returned a non-zero exit code and $ignoreErrors was enabled.
     */
    public function execute($command, Node $node, Deployment $deployment, bool $ignoreErrors = false, bool $logOutput = true)
    {
        if ($node->isLocalhost()) {
            [$exitCode, $returnedOutput] = $this->executeLocalCommand($command, $deployment, $logOutput);
        } else {
            [$exitCode, $returnedOutput] = $this->executeRemoteCommand($command, $node, $deployment, $logOutput);
        }
        if (!$ignoreErrors && $exitCode !== 0) {
            $this->logger->warning(rtrim($returnedOutput));
            throw new TaskExecutionException('Command returned non-zero return code: ' . $exitCode, 1311007746);
        }
        return $exitCode === 0 ? $returnedOutput : false;
    }

    /**
     * Simulate a command by just outputting what would be executed
     *
     * @param array|string $command
     */
    public function simulate($command, Node $node, Deployment $deployment): bool
    {
        if ($node->isLocalhost()) {
            $command = $this->prepareCommand($command);
            $this->logger->debug('... (localhost): "' . $command . '"');
        } else {
            $command = $this->prepareCommand($command);
            $this->logger->debug('... $' . $node->getName() . ': "' . $command . '"');
        }
        return true;
    }

    /**
     * Execute or simulate a command (if the deployment is in dry run mode)
     *
     * @param array|string $command
     * @param bool $ignoreErrors
     * @param bool $logOutput true if the output of the command should be logged
     * @return mixed false if command failed or command output as string
     */
    public function executeOrSimulate($command, Node $node, Deployment $deployment, $ignoreErrors = false, $logOutput = true)
    {
        if (!$deployment->isDryRun()) {
            return $this->execute($command, $node, $deployment, $ignoreErrors, $logOutput);
        }
        return $this->simulate($command, $node, $deployment);
    }

    /**
     * Execute a shell command locally
     *
     * @param array|string $command
     * @param bool $logOutput TRUE if the output of the command should be logged
     */
    protected function executeLocalCommand($command, Deployment $deployment, bool $logOutput = true): array
    {
        $command = $this->prepareCommand($command);
        $this->logger->debug('(localhost): "' . $command . '"');

        return $this->executeProcess($deployment, $command, $logOutput, '> ');
    }

    /**
     * Execute a shell command via SSH
     *
     * @param array|string $command
     * @param bool $logOutput TRUE if the output of the command should be logged
     * @return array
     */
    protected function executeRemoteCommand($command, Node $node, Deployment $deployment, bool $logOutput = true)
    {
        $command = $this->prepareCommand($command);
        $this->logger->debug('$' . $node->getName() . ': "' . $command . '"');

        if ($node->hasOption('remoteCommandExecutionHandler')) {
            $remoteCommandExecutionHandler = $node->getOption('remoteCommandExecutionHandler');
            /** @var $remoteCommandExecutionHandler callable */
            return $remoteCommandExecutionHandler($this, $command, $node, $deployment, $logOutput);
        }

        $username = $node->getUsername();
        if (!empty($username)) {
            $username .= '@';
        }
        $hostname = $node->hasOption('hostname') ? $node->getHostname() : '';

        // TODO Get SSH options from node or deployment
        $sshOptions = ['-A'];
        if ($node->hasOption('port')) {
            $sshOptions[] = '-p ' . escapeshellarg((string)$node->getOption('port'));
        }
        if ($node->hasOption('password')) {
            $sshOptions[] = '-o PubkeyAuthentication=no';
        }
        if ($node->hasOption('privateKeyFile')) {
            $sshOptions[] = '-i ' . escapeshellarg($node->getOption('privateKeyFile'));
        }

        $sshCommand = 'ssh ' . implode(' ', $sshOptions) . ' ' . escapeshellarg($username . $hostname) . ' ' . escapeshellarg($command);

        if ($node->hasOption('password')) {
            $passwordSshLoginScriptPathAndFilename = Files::concatenatePaths([dirname(__DIR__, 3), 'Resources', 'Private/Scripts/PasswordSshLogin.expect']);
            if (Phar::running() !== '') {
                $passwordSshLoginScriptContents = file_get_contents($passwordSshLoginScriptPathAndFilename);
                $passwordSshLoginScriptPathAndFilename = Files::concatenatePaths([$deployment->getTemporaryPath(), 'PasswordSshLogin.expect']);
                file_put_contents($passwordSshLoginScriptPathAndFilename, $passwordSshLoginScriptContents);
            }
            $sshCommand = sprintf('expect %s %s %s', escapeshellarg($passwordSshLoginScriptPathAndFilename), escapeshellarg($node->getOption('password')), $sshCommand);
        }
        $success = $this->executeProcess($deployment, $sshCommand, $logOutput, '    > ');
        if (!isset($passwordSshLoginScriptPathAndFilename)) {
            return $success;
        }
        if (Phar::running() === '') {
            return $success;
        }
        unlink($passwordSshLoginScriptPathAndFilename);
        return $success;
    }

    /**
     * Open a process with symfony/process and process each line by logging and
     * collecting its output.
     *
     * @return array The exit code of the command and the returned output
     */
    public function executeProcess(Deployment $deployment, string $command, bool $logOutput, string $logPrefix): array
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);
        $callback = null;
        if ($logOutput) {
            $callback = function ($type, $data) use ($logPrefix): void {
                if ($type === Process::OUT) {
                    $this->logger->debug($logPrefix . trim($data));
                } elseif ($type === Process::ERR) {
                    $this->logger->error($logPrefix . trim($data));
                }
            };
        }
        $exitCode = $process->run($callback);
        return [$exitCode, trim($process->getOutput())];
    }

    /**
     * Prepare a command
     *
     * @param array|string|null $command
     * @return string
     * @throws TaskExecutionException
     */
    protected function prepareCommand($command): string
    {
        if (is_string($command)) {
            return trim($command);
        }
        if (is_array($command)) {
            return implode(' && ', $command);
        }
        throw new TaskExecutionException('Command must be string or array, ' . gettype($command) . ' given.', 1312454906);
    }
}
