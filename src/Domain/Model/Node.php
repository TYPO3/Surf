<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Model;

/**
 * A Node
 */
class Node
{
    protected string $name;

    /**
     * Options for this node
     *
     * username: SSH username for connecting to this node (optional)
     * port: SSH port for connecting to the node (optional)
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

    public function getHostname(): string
    {
        return $this->getOption('hostname');
    }

    public function setHostname(string $hostname): Node
    {
        return $this->setOption('hostname', $hostname);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

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
        return $this->options[$key];
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

    /**
     * @return int|null
     */
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
