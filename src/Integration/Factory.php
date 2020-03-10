<?php

namespace TYPO3\Surf\Integration;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleHandler;
use TYPO3\Surf\Command\DeployCommand;
use TYPO3\Surf\Command\DescribeCommand;
use TYPO3\Surf\Command\RollbackCommand;
use TYPO3\Surf\Command\SelfUpdateCommand;
use TYPO3\Surf\Command\ShowCommand;
use TYPO3\Surf\Command\SimulateCommand;
use TYPO3\Surf\Domain\Filesystem\Filesystem;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\FailedDeployment;
use TYPO3\Surf\Exception\InvalidConfigurationException;

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
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function __construct(FilesystemInterface $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    /**
     * @inheritDoc
     */
    public function createCommands(): array
    {
        return [
            new ShowCommand(),
            new SimulateCommand(),
            new DescribeCommand(),
            new DeployCommand(),
            new RollbackCommand(),
            new SelfUpdateCommand(),
        ];
    }

    public function createOutput(): OutputInterface
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
            $this->output->getFormatter()->setStyle('b', new OutputFormatterStyle(null, null, ['bold']));
            $this->output->getFormatter()->setStyle('i', new OutputFormatterStyle('black', 'white'));
            $this->output->getFormatter()->setStyle('u', new OutputFormatterStyle(null, null, ['underscore']));
            $this->output->getFormatter()->setStyle('em', new OutputFormatterStyle(null, null, ['reverse']));
            $this->output->getFormatter()->setStyle('strike', new OutputFormatterStyle(null, null, ['conceal']));
            $this->output->getFormatter()->setStyle('success', new OutputFormatterStyle('green'));
            $this->output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));
            $this->output->getFormatter()->setStyle('notice', new OutputFormatterStyle('yellow'));
            $this->output->getFormatter()->setStyle('info', new OutputFormatterStyle('white', null, ['bold']));
            $this->output->getFormatter()->setStyle('debug', new OutputFormatterStyle('white'));
        }

        return $this->output;
    }

    public function getDeployment(string $deploymentName, string $configurationPath = null, bool $simulateDeployment = true, bool $initialize = true, bool $forceDeployment = false): Deployment
    {
        $deployment = $this->createDeployment($deploymentName, $configurationPath);
        if ($deployment->getLogger() === null) {
            if (! $simulateDeployment) {
                $logFilePath = Files::concatenatePaths([$this->getWorkspacesBasePath($configurationPath), 'logs', $deployment->getName() . '.log']);
                $this->createLogger()->pushHandler(new StreamHandler($logFilePath));
            }
            $deployment->setLogger($this->createLogger());
        }

        $deployment->setForceRun($forceDeployment);

        if ($initialize) {
            $deployment->initialize();
        }

        $deployment->setDryRun($simulateDeployment);

        return $deployment;
    }

    /**
     * @inheritDoc
     */
    public function getDeploymentNames(string $path = null): array
    {
        $path = $this->getDeploymentsBasePath($path);
        $files = $this->filesystem->glob(Files::concatenatePaths([$path, '*.php']));

        return array_map(static function ($file) use ($path) {
            return substr($file, strlen($path) + 1, -4);
        }, $files);
    }

    /**
     * @inheritDoc
     */
    public function getDeploymentsBasePath(string $path = null): string
    {
        $localDeploymentDescription = $this->filesystem->getRealPath('./.surf');
        if (! $path && $this->filesystem->isDirectory($localDeploymentDescription)) {
            $path = $localDeploymentDescription;
        }
        $path = $path ?: Files::concatenatePaths([$this->getHomeDirectory(), 'deployments']);
        $this->ensureDirectoryExists($path);

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function getWorkspacesBasePath(string $path = null): string
    {
        $workspacesBasePath = getenv('SURF_WORKSPACE');

        if ($workspacesBasePath) {
            $this->ensureDirectoryExists($workspacesBasePath);

            return $workspacesBasePath;
        }

        $path = $path ?: $this->getHomeDirectory();

        if (defined('PHP_WINDOWS_VERSION_MAJOR') && $workspacesBasePath = getenv('LOCALAPPDATA')) {
            $workspacesBasePath = Files::concatenatePaths([$workspacesBasePath, 'Surf']);
            $this->ensureDirectoryExists($workspacesBasePath);

            return $workspacesBasePath;
        }

        $workspacesBasePath = Files::concatenatePaths([$path, 'workspace']);
        $this->ensureDirectoryExists($workspacesBasePath);

        return $workspacesBasePath;
    }

    /**
     * Get a deployment object by deployment name
     *
     * Looks up the deployment in directory ./.surf/[deploymentName].php
     *
     * The script has access to a deployment object as "$deployment". This could change
     * in the future.
     */
    protected function createDeployment(string $deploymentName, string $path = null): Deployment
    {
        $deploymentConfigurationPath = $this->getDeploymentsBasePath($path);
        $workspacesBasePath = $this->getWorkspacesBasePath();

        if (empty($deploymentName)) {
            $deploymentNames = $this->getDeploymentNames($path);

            if (count($deploymentNames) !== 1) {
                throw InvalidConfigurationException::createNoDeploymentNameGiven();
            }

            $deploymentName = array_pop($deploymentNames);
        }

        $deploymentPathAndFilename = Files::concatenatePaths([$deploymentConfigurationPath, $deploymentName . '.php']);
        if ($this->filesystem->fileExists($deploymentPathAndFilename)) {
            $deployment = new Deployment($deploymentName);
            $deployment->setDeploymentBasePath($deploymentConfigurationPath);
            $deployment->setWorkspacesBasePath($workspacesBasePath);
            $tempPath = Files::concatenatePaths([$workspacesBasePath, $deploymentName]);
            $this->ensureDirectoryExists($tempPath);
            $deployment->setTemporaryPath($tempPath);

            $this->filesystem->requireFile($deploymentPathAndFilename);
        } else {
            $this->createLogger()->error(sprintf("The deployment file %s does not exist.\n", $deploymentPathAndFilename));
            $deployment = new FailedDeployment();
        }

        return $deployment;
    }

    protected function getHomeDirectory(): string
    {
        $home = getenv('SURF_HOME');

        if ($home) {
            $this->ensureDirectoryExists($home);

            return $home;
        }

        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            if (! getenv('APPDATA')) {
                throw new RuntimeException('The APPDATA or SURF_HOME environment variable must be set for composer to run correctly');
            }
            $home = Files::concatenatePaths([getenv('APPDATA'), 'Surf']);

            $this->ensureDirectoryExists($home);

            return $home;
        }

        if (! getenv('HOME')) {
            throw new RuntimeException('The HOME or SURF_HOME environment variable must be set for composer to run correctly');
        }
        $home = Files::concatenatePaths([getenv('HOME'), '.surf']);
        $this->ensureDirectoryExists($home);

        return $home;
    }

    protected function createLogger(): Logger
    {
        if ($this->logger === null) {
            $consoleHandler = new ConsoleHandler($this->createOutput());
            $this->logger = new Logger('TYPO3 Surf', [$consoleHandler]);
        }

        return $this->logger;
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (! $this->filesystem->fileExists($directory) && ! $this->filesystem->createDirectory($directory) && ! $this->filesystem->isDirectory($directory)) {
            throw new InvalidConfigurationException(sprintf('Directory "%s" cannot be created!', $directory), 1451862775);
        }
    }
}
