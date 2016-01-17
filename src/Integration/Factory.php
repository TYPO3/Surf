<?php
namespace TYPO3\Surf\Integration;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleHandler;
use TYPO3\Surf\Command\DeployCommand;
use TYPO3\Surf\Command\DescribeCommand;
use TYPO3\Surf\Command\MigrateCommand;
use TYPO3\Surf\Command\ShowCommand;
use TYPO3\Surf\Command\SimulateCommand;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Exception\InvalidConfigurationException;

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
            new MigrateCommand(),
        );
    }

    /**
     * @return ConsoleOutput
     */
    public function createOutput()
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
            $this->output->getFormatter()->setStyle('b', new OutputFormatterStyle(null, null, array('bold')));
            $this->output->getFormatter()->setStyle('i', new OutputFormatterStyle('black', 'white'));
            $this->output->getFormatter()->setStyle('u', new OutputFormatterStyle(null, null, array('underscore')));
            $this->output->getFormatter()->setStyle('em', new OutputFormatterStyle(null, null, array('reverse')));
            $this->output->getFormatter()->setStyle('strike', new OutputFormatterStyle(null, null, array('conceal')));
            $this->output->getFormatter()->setStyle('success', new OutputFormatterStyle('green'));
            $this->output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));
            $this->output->getFormatter()->setStyle('notice', new OutputFormatterStyle('yellow'));
            $this->output->getFormatter()->setStyle('info', new OutputFormatterStyle('white', null, array('bold')));
            $this->output->getFormatter()->setStyle('debug', new OutputFormatterStyle('white'));
        }

        return $this->output;
    }

    /**
     * @param string $deploymentName
     * @param string $configurationPath
     * @param bool $simulateDeployment
     * @return Deployment
     */
    public function getDeployment($deploymentName, $configurationPath = null, $simulateDeployment = true)
    {
        $deployment = $this->createDeployment($deploymentName, $configurationPath);
        if ($deployment->getLogger() === null) {
            $logger = $this->createLogger();
            if (!$simulateDeployment) {
                $logPath = $this->getWorkspacesBasePath($configurationPath) . '/logs';
                $logger->pushHandler(new StreamHandler($logPath . '/' . $deployment->getName() . '.log'));
            }
            $deployment->setLogger($logger);
        }
        $deployment->initialize();

        return $deployment;
    }

    /**
     * Get available deployment names
     *
     * Will look up all .php files in the directory ./.surf/ or the given path if specified.
     *
     * @param string $path
     * @return array
     */
    public function getDeploymentNames($path = null)
    {
        $path = $this->getDeploymentsBasePath($path);
        $files = glob($path . '/*.php');
        return array_map(function ($file) use ($path) {
            return substr($file, strlen($path) + 1, -4);
        }, $files);
    }

    /**
     * Get the root path of the surf deployment declarations
     *
     * This defaults to ./.surf if a NULL path is given.
     *
     * @param string $path An absolute path (optional)
     * @return string The configuration root path without a trailing slash.
     */
    public function getDeploymentsBasePath($path = null)
    {
        $localDeploymentDescription = @realpath('./.surf');
        if (!$path && is_dir($localDeploymentDescription)) {
            $path = $localDeploymentDescription;
        }
        $path = $path ?: ($this->getHomeDir() . '/deployments');
        $this->ensureDirectoryExists($path);
        return $path;
    }

    /**
     * Get the base path to local workspaces
     *
     * @param string $path An absolute path (optional)
     * @return string The workspaces base path without a trailing slash.
     */
    public function getWorkspacesBasePath($path = null)
    {
        $workspacesBasePath = getenv('SURF_WORKSPACE');
        if (!$workspacesBasePath) {
            $path = $path ?: $this->getHomeDir();
            if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
                if ($workspacesBasePath = getenv('LOCALAPPDATA')) {
                    $workspacesBasePath .= '/Surf';
                } else {
                    $workspacesBasePath = $path . '/workspace';
                }
                $workspacesBasePath = strtr($workspacesBasePath, '\\', '/');
            } else {
                $workspacesBasePath = $path . '/workspace';
            }
        }
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
     *
     * @param string $deploymentName
     * @param string $path
     * @return \TYPO3\Surf\Domain\Model\Deployment
     * @throws InvalidConfigurationException
     */
    protected function createDeployment($deploymentName, $path = null)
    {
        $deploymentConfigurationPath = $this->getDeploymentsBasePath($path);
        $workspacesBasePath = $this->getWorkspacesBasePath();

        if (empty($deploymentName)) {
            $deploymentNames = $this->getDeploymentNames($path);
            if (count($deploymentNames) !== 1) {
                throw new InvalidConfigurationException('No deployment name given!', 1451865016);
            }
            $deploymentName = array_pop($deploymentNames);
        }

        $deploymentPathAndFilename = $deploymentConfigurationPath . '/' . $deploymentName . '.php';
        if (!file_exists($deploymentPathAndFilename)) {
            exit(sprintf("The deployment file %s does not exist.\n", $deploymentPathAndFilename));
        }

        $deployment = new Deployment($deploymentName);
        $deployment->setDeploymentBasePath($deploymentConfigurationPath);
        $deployment->setWorkspacesBasePath($workspacesBasePath);
        require($deploymentPathAndFilename);
        return $deployment;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getHomeDir()
    {
        $home = getenv('SURF_HOME');
        if (!$home) {
            if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
                if (!getenv('APPDATA')) {
                    throw new \RuntimeException('The APPDATA or SURF_HOME environment variable must be set for composer to run correctly');
                }
                $home = strtr(getenv('APPDATA'), '\\', '/') . '/Surf';
            } else {
                if (!getenv('HOME')) {
                    throw new \RuntimeException('The HOME or SURF_HOME environment variable must be set for composer to run correctly');
                }
                $home = rtrim(getenv('HOME'), '/') . '/.surf';
            }
        }
        $this->ensureDirectoryExists($home);
        return $home;
    }

    /**
     * @return Logger
     */
    protected function createLogger()
    {
        if ($this->logger === null) {
            $consoleHandler = new ConsoleHandler($this->createOutput());
            $this->logger = new Logger('TYPO3 Surf', array($consoleHandler));
        }
        return $this->logger;
    }

    /**
     * @param string $dir
     * @throws InvalidConfigurationException
     */
    protected function ensureDirectoryExists($dir)
    {
        if (!file_exists($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new InvalidConfigurationException(sprintf('Directory "%s" cannot be created!', $dir), 1451862775);
        }
    }
}
