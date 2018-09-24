<?php
namespace TYPO3\Surf\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

/**
 * A Node
 */
class Node
{
    /**
     * The name
     * @var string
     */
    protected $name;

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
    public function getOptions()
    {
        return $this->options;
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
    public function getOption($key)
    {
        return $this->options[$key];
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
