<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\TYPO3\CMS;

use PharIo\Version\InvalidVersionException;
use PharIo\Version\Version;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Abstract task for any remote TYPO3 CMS cli action
 */
abstract class AbstractCliTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * The working directory. Either local or remote, and probably in a special application root directory
     */
    protected ?string $workingDirectory = null;

    /**
     * Localhost or deployment target node
     */
    protected ?Node $targetNode = null;

    /**
     * @return bool|mixed
     */
    protected function executeCliCommand(array $cliArguments, Node $node, CMS $application, Deployment $deployment, array $options = [])
    {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $phpBinaryPathAndFilename = $options['phpBinaryPathAndFilename'] ?? 'php';
        $commandPrefix = '';
        if (isset($options['context'])) {
            $commandPrefix = 'TYPO3_CONTEXT=' . escapeshellarg($options['context']) . ' ';
        }
        $commandPrefix .= $phpBinaryPathAndFilename . ' ';

        if (!$this->targetNode instanceof Node) {
            return false;
        }

        return $this->shell->executeOrSimulate([
            'cd ' . escapeshellarg((string)$this->workingDirectory),
            $commandPrefix . implode(' ', array_map('escapeshellarg', $cliArguments))
        ], $this->targetNode, $deployment);
    }

    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }

    protected function determineWorkingDirectoryAndTargetNode(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        if (!isset($this->workingDirectory, $this->targetNode)) {
            if (isset($options['useApplicationWorkspace']) && $options['useApplicationWorkspace'] === true) {
                $this->workingDirectory = $deployment->getWorkspacePath($application);
                $node = $deployment->createLocalhostNode();
            } else {
                $this->workingDirectory = $deployment->getApplicationReleasePath($node);
            }
            $this->targetNode = $node;
        }
    }

    protected function getAvailableCliPackage(Node $node, CMS $application, Deployment $deployment, array $options = []): ?string
    {
        try {
            $this->getTypo3ConsoleScriptFileName($node, $application, $deployment, $options);
            return 'typo3_console';
        } catch (InvalidConfigurationException $e) {
            return null;
        }
    }

    protected function getTypo3CoreCliFileName(Node $node, CMS $application, Deployment $deployment, array $options = []): string
    {
        if (!isset($options['typo3CliFileName'])) {
            throw InvalidConfigurationException::createTypo3CoreCliNotFound(get_class($this));
        }

        if (false === strpos($options['typo3CliFileName'], 'typo3')) {
            throw InvalidConfigurationException::createTypo3CoreCliNotFound(get_class($this));
        }

        if (false === $this->fileExists($options['typo3CliFileName'], $node, $application, $deployment, $options)) {
            throw InvalidConfigurationException::createTypo3CoreCliNotFound(get_class($this));
        }

        return $options['typo3CliFileName'];
    }

    protected function getTypo3ConsoleScriptFileName(Node $node, CMS $application, Deployment $deployment, array $options = []): string
    {
        if (!isset($options['scriptFileName'])) {
            throw InvalidConfigurationException::createTypo3ConsoleScriptNotFound(get_class($this));
        }

        if (false === strpos($options['scriptFileName'], 'typo3cms')) {
            throw InvalidConfigurationException::createTypo3ConsoleScriptNotFound(get_class($this));
        }

        if (false === $this->fileExists($options['scriptFileName'], $node, $application, $deployment, $options)) {
            throw InvalidConfigurationException::createTypo3ConsoleScriptNotFound(get_class($this));
        }

        return $options['scriptFileName'];
    }

    protected function getTypo3CoreVersion(Node $node, CMS $application, Deployment $deployment, array $options): Version
    {
        $scriptFileName = $this->getTypo3CoreCliFileName($node, $application, $deployment, $options);

        $commandArguments = [$scriptFileName, '--version'];

        $output = $this->executeCliCommand(
            $commandArguments,
            $node,
            $application,
            $deployment,
            $options
        );

        preg_match('/TYPO3 CMS (.*) \(/', $output, $matches);

        try {
            return new Version($matches[1]);
        } catch (InvalidVersionException $e) {
            return new Version('0.0.0');
        }
    }

    protected function getTypo3ConsoleVersion(Node $node, CMS $application, Deployment $deployment, array $options): Version
    {
        $scriptFileName = $this->getTypo3ConsoleScriptFileName($node, $application, $deployment, $options);

        $commandArguments = [$scriptFileName, '--version'];

        $output = $this->executeCliCommand(
            $commandArguments,
            $node,
            $application,
            $deployment,
            $options
        );

        // return version in simulation
        if ($output === true) {
            return new Version('0.0.0');
        }

        [$versionLine] = explode("\n", $output);

        $version = trim(substr($versionLine, strlen('TYPO3 Console')) ?: '');

        try {
            return new Version($version);
        } catch (InvalidVersionException $e) {
            return new Version('0.0.0');
        }
    }

    protected function fileExists(string $pathAndFileName, Node $node, CMS $application, Deployment $deployment, array $options = []): bool
    {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $pathAndFileName = $this->workingDirectory . '/' . $pathAndFileName;
        return $this->shell->executeOrSimulate('test -f ' . escapeshellarg($pathAndFileName), $this->targetNode, $deployment, true) !== false;
    }
}
