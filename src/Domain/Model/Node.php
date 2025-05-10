<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Model;

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
     */
    protected string $name;

    /**
     * The deployment path on the node
     */
    protected string $deploymentPath = '';

    /**
     * The relative releases directory on a node
     *
     * @var string
     */
    protected string $releasesDirectory = 'releases';

    /**
     * Options for this node
     *
     * username: SSH username for connecting to this node (optional)
     * port: SSH port for connecting to the node (optional)
     *
     * @var array{hostname?: string, username?: string, port?: int}
     */
    protected array $options = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDeploymentPath(): string
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
     */
    public function getHostname(): ?string
    {
        return $this->getOption('hostname');
    }

    public function setHostname(string $hostname): Node
    {
        return $this->setOption('hostname', $hostname);
    }

    /**
     * @return array<string, mixed>
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
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
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
                return $this->options[$key] ?? null;
        }
    }

    /**
     * @param mixed $value
     */
    public function setOption(string $key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    public function setPort(int $port): self
    {
        $this->setOption('port', $port);
        return $this;
    }

    public function setRemoteCommandExecutionHandler(callable $remoteCommandExecutionHandler): self
    {
        $this->setOption('remoteCommandExecutionHandler', $remoteCommandExecutionHandler);
        return $this;
    }

    public function getRemoteCommandExecutionHandler(): ?callable
    {
        if ($this->hasOption('remoteCommandExecutionHandler')) {
            return $this->getOption('remoteCommandExecutionHandler');
        }
        return null;
    }

    public function setUsername(string $username): self
    {
        $this->setOption('username', $username);
        return $this;
    }

    public function getUsername(): ?string
    {
        if ($this->hasOption('username')) {
            return $this->getOption('username');
        }
        return null;
    }

    public function getPort(): ?int
    {
        if ($this->hasOption('port')) {
            return $this->getOption('port');
        }

        return null;
    }

    public function isLocalhost(): bool
    {
        return $this->getOption('hostname') === 'localhost';
    }

    public function onLocalhost(): self
    {
        $this->setOption('hostname', 'localhost');
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
