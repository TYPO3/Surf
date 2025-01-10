<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Integration;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Neos\Utility\Files;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\FailedDeployment;
use TYPO3\Surf\Exception\InvalidConfigurationException;

class Factory implements FactoryInterface
{
    protected OutputInterface $output;

    protected LoggerInterface $logger;

    protected FilesystemInterface $filesystem;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container, FilesystemInterface $filesystem, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    public function getDeployment(string $deploymentName, string $configurationPath = null, bool $simulateDeployment = true, bool $initialize = true, bool $forceDeployment = false): Deployment
    {
        $deployment = $this->createDeployment($deploymentName, $configurationPath);

        if (! $simulateDeployment) {
            $logFilePath = Files::concatenatePaths([$this->getWorkspacesBasePath($configurationPath), 'logs', $deployment->getName() . '.log']);
            if ($this->logger instanceof Logger) {
                $this->logger->pushHandler(new StreamHandler($logFilePath));
            }
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

        return array_map(static fn ($file): string => substr($file, strlen($path) + 1, -4), $files);
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

        if (! $this->filesystem->fileExists($deploymentPathAndFilename)) {
            // Check if file exists in home-dir configurations instead
            $deploymentPathAndFilename = Files::concatenatePaths([$this->getHomeDirectory(), 'deployments', $deploymentName . '.php']);
        }

        if (! $this->filesystem->fileExists($deploymentPathAndFilename)) {
            $this->logger->error(sprintf("The deployment file %s does not exist.\n", $deploymentPathAndFilename));
            $deployment = new FailedDeployment($this->container, $deploymentName);
            $deployment->setLogger($this->logger);

            return $deployment;
        }

        $deployment = new Deployment($this->container, $deploymentName);
        $deployment->setLogger($this->logger);
        $deployment->setDeploymentBasePath($deploymentConfigurationPath);
        $deployment->setWorkspacesBasePath($workspacesBasePath);

        $tempPath = Files::concatenatePaths([$workspacesBasePath, $deploymentName]);
        $this->ensureDirectoryExists($tempPath);
        $deployment->setTemporaryPath($tempPath);

        $container = $this->container;

        require($deploymentPathAndFilename);

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

    protected function ensureDirectoryExists(string $directory): void
    {
        if ($this->filesystem->fileExists($directory)) {
            return;
        }
        if ($this->filesystem->createDirectory($directory)) {
            return;
        }
        if ($this->filesystem->isDirectory($directory)) {
            return;
        }
        throw new InvalidConfigurationException(sprintf('Directory "%s" cannot be created!', $directory), 1451862775);
    }
}
