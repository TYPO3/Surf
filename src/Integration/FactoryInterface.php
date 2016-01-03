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
     * @return LoggerInterface
     */
    public function createLogger();

    /**
     * Get the deployment object with the specified name
     *
     * @param string $deploymentName
     * @param string|null $configurationPath
     * @param bool $simulateDeployment
     * @return Deployment
     */
    public function getDeployment($deploymentName, $configurationPath = null, $simulateDeployment = true);

    /**
     * Get available deployment names
     *
     * Will look up all .php files in the directory ./.surf/ or the given path if specified.
     *
     * @param string $path
     * @return array
     */
    public function getDeploymentNames($path = null);

    /**
     * Get the root path of the surf deployment declarations
     *
     * This defaults to ./.surf if a NULL path is given.
     *
     * @param string $path An absolute path (optional)
     * @return string The configuration root path without a trailing slash.
     */
    public function getDeploymentsBasePath($path = null);

    /**
     * Get the base path to local workspaces
     *
     * @param string $path An absolute path (optional)
     * @return string The workspaces base path without a trailing slash.
     */
    public function getWorkspacesBasePath($path = null);

}