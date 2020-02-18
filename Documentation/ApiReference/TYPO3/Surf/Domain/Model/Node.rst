--------------------------------
TYPO3\\Surf\\Domain\\Model\\Node
--------------------------------

.. php:namespace: TYPO3\\Surf\\Domain\\Model

.. php:class:: Node

    A Node

    .. php:attr:: name

        protected string

        The name

    .. php:attr:: options

        protected array

        Options for this node

        username: SSH username for connecting to this node (optional)
        port: SSH port for connecting to the node (optional)

    .. php:method:: __construct($name)

        Constructor

        :type $name: string
        :param $name:

    .. php:method:: getName()

        Get the Node's name

        :returns: string The Node's name

    .. php:method:: getHostname()

        Get the Node's hostname

        :returns: string The Node's hostname

    .. php:method:: setHostname($hostname)

        Sets this Node's hostname

        :type $hostname: string
        :param $hostname: The Node's hostname
        :returns: Node

    .. php:method:: getOptions()

        Get the Node's options

        :returns: array The Node's options

    .. php:method:: setOptions($options)

        Sets this Node's options

        :type $options: array
        :param $options: The Node's options
        :returns: Node

    .. php:method:: getOption($key)

        :type $key: string
        :param $key:
        :returns: mixed

    .. php:method:: setOption($key, $value)

        :type $key: string
        :param $key:
        :type $value: mixed
        :param $value:
        :returns: Node

    .. php:method:: hasOption($key)

        :type $key: string
        :param $key:
        :returns: bool

    .. php:method:: setPort($port)

        :type $port: string
        :param $port:
        :returns: Node

    .. php:method:: setRemoteCommandExecutionHandler($remoteCommandExecutionHandler)

        :type $remoteCommandExecutionHandler: callable
        :param $remoteCommandExecutionHandler:
        :returns: Node

    .. php:method:: getRemoteCommandExecutionHandler()

        :returns: callable|null

    .. php:method:: setUsername($username)

        :type $username: string
        :param $username:
        :returns: Node

    .. php:method:: getUsername()

        :returns: string|null

    .. php:method:: getPort()

        :returns: string|null

    .. php:method:: isLocalhost()

        :returns: bool TRUE if this node is the localhost

    .. php:method:: onLocalhost()

        :returns: Node

    .. php:method:: __toString()

        :returns: string
