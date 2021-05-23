<?php
namespace TYPO3\Surf\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\InvalidConfigurationException;

class Node
{
    /**
     * default directory name for shared directory
     *
     * @const
     */
    public const DEFAULT_SHARED_DIR = 'shared';

    private const FORBIDDEN_SHARED_REGULAR_EXPRESSION = '/(^|\/)\.\.(\/|$)/';

    /**
     * The name
     * @var string
     */
    protected $name;

    /**
     * The deployment path on the node
     *
     * @var string
     */
    protected $deploymentPath;

    /**
     * The relative releases directory on a node
     *
     * @var string
     */
    protected $releasesDirectory = 'releases';

    /**
     * Options for this node
     *
     * username: SSH username for connecting to this node (optional)
     * port: SSH port for connecting to the node (optional)
     *
     * @var array
     */
    protected $options = [];

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get the Node's name
     *
     * @return string The Node's name
     */
    public function getName()
    {
        return $this->name;
    }

    public function getDeploymentPath(): ?string
    {
        return $this->deploymentPath;
    }

    public function setDeploymentPath(string $deploymentPath): self
    {
        $this->deploymentPath = $deploymentPath;
        return $this;
    }

    public function getReleasesDirectory(): string
    {
        return $this->releasesDirectory;
    }

    public function setReleasesDirectory(string $releasesDirectory): self
    {
        if (preg_match(self::FORBIDDEN_SHARED_REGULAR_EXPRESSION, $releasesDirectory)) {
            throw new InvalidConfigurationException(
                sprintf('"../" is not allowed in the releases directory "%s"', $releasesDirectory),
                1380870750
            );
        }
        $this->releasesDirectory = trim($releasesDirectory, '/');
        return $this;
    }

    /**
     * Get the path for shared resources for this application
     *
     * This path defaults to a directory "shared" below the deployment path.
     */
    public function getSharedPath(): string
    {
        return $this->getDeploymentPath() . '/' . $this->getSharedDirectory();
    }

    /**
     * Returns the shared directory
     *
     * takes directory name from option "sharedDirectory"
     * if option is not set or empty constant DEFAULT_SHARED_DIR "shared" is used
     */
    public function getSharedDirectory(): string
    {
        $result = self::DEFAULT_SHARED_DIR;
        if ($this->hasOption('sharedDirectory') && !empty($this->getOption('sharedDirectory'))) {
            $sharedPath = $this->getOption('sharedDirectory');
            if (preg_match(self::FORBIDDEN_SHARED_REGULAR_EXPRESSION, $sharedPath)) {
                throw new InvalidConfigurationException(
                    sprintf(
                        'Relative constructs as "../" are not allowed in option "sharedDirectory". Given option: "%s"',
                        $sharedPath
                    ),
                    1490107183141
                );
            }
            $result = rtrim($sharedPath, '/');
        }
        return $result;
    }

    /**
     * Returns path to the directory with releases
     */
    public function getReleasesPath(): string
    {
        return rtrim($this->getDeploymentPath() . '/' . $this->getReleasesDirectory(), '/');
    }

    /**
     * Get the Node's hostname
     *
     * @return string The Node's hostname
     */
    public function getHostname()
    {
        return $this->getOption('hostname');
    }

    /**
     * Sets this Node's hostname
     *
     * @param string $hostname The Node's hostname
     * @return Node
     */
    public function setHostname($hostname)
    {
        return $this->setOption('hostname', $hostname);
    }

    /**
     * Get the Node's options
     *
     * @return array The Node's options
     */
    public function getOptions(): array
    {
        return array_merge($this->options, [
            'deploymentPath' => $this->getDeploymentPath(),
            'releasesPath' => $this->getReleasesPath(),
            'sharedPath' => $this->getSharedPath()
        ]);
    }

    /**
     * Sets this Node's options
     *
     * @param array $options The Node's options
     * @return Node
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getOption(string $key)
    {
        switch ($key) {
            case 'deploymentPath':
                return $this->getDeploymentPath();
            case 'releasesPath':
                return $this->getReleasesPath();
            case 'sharedPath':
                return $this->getSharedPath();
            default:
                return $this->options[$key];
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Node
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasOption($key)
    {
        return isset($this->options[$key]);
    }

    /**
     * @param string $port
     *
     * @return Node
     */
    public function setPort($port)
    {
        $this->setOption('port', $port);
        return $this;
    }

    /**
     * @param callable $remoteCommandExecutionHandler
     *
     * @return Node
     */
    public function setRemoteCommandExecutionHandler(callable $remoteCommandExecutionHandler)
    {
        $this->setOption('remoteCommandExecutionHandler', $remoteCommandExecutionHandler);
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getRemoteCommandExecutionHandler()
    {
        if ($this->hasOption('remoteCommandExecutionHandler')) {
            return $this->getOption('remoteCommandExecutionHandler');
        }
        return null;
    }

    /**
     * @param string $username
     *
     * @return Node
     */
    public function setUsername($username)
    {
        $this->setOption('username', $username);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername()
    {
        if ($this->hasOption('username')) {
            return $this->getOption('username');
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getPort()
    {
        if ($this->hasOption('port')) {
            return $this->getOption('port');
        }

        return null;
    }

    /**
     * @return bool TRUE if this node is the localhost
     */
    public function isLocalhost()
    {
        return $this->getOption('hostname') === 'localhost';
    }

    /**
     * @return Node
     */
    public function onLocalhost()
    {
        $this->setOption('hostname', 'localhost');
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
