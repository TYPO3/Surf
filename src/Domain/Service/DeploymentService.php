<?php
namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A deployment service
 *
 */
class DeploymentService
{
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
     */
    public function getDeployment($deploymentName, $path = null)
    {
        $homeDir = $path ?: $this->getHomeDir();
        $deploymentConfigurationPath = $this->getDeploymentsBasePath($homeDir);
        $workspacesBasePath = $this->getWorkspacesBasePath($homeDir);
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
     * Get the root path of the surf deployment declarations
     *
     * This defaults to ./.surf if a NULL path is given.
     *
     * @param string $path An absolute path (optional)
     * @return string The configuration root path without a trailing slash.
     */
    public function getDeploymentsBasePath($path = null)
    {
        $path = realpath($path ?: $this->getHomeDir());
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

        return $workspacesBasePath;
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

        return $home;
    }

}
