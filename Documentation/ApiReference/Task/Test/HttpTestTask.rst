-------------------------------------
TYPO3\\Surf\\Task\\Test\\HttpTestTask
-------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Test

.. php:class:: HttpTestTask

    A task for testing HTTP request

    This task could be used to do smoke-tests against web applications in release (e.g. on a virtual host mounted on the "next" symlink).

    .. php:attr:: shell

        protected ShellCommandService

    .. php:method:: execute(Node $node, Application $application, Deployment $deployment, $options = [])

        Execute this task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: assertExpectedStatus($options, $result)

        :type $options: array
        :param $options:
        :type $result: array
        :param $result:

    .. php:method:: assertExpectedHeaders($options, $result)

        :type $options: array
        :param $options:
        :type $result: array
        :param $result:

    .. php:method:: assertExpectedRegexp($options, $result)

        :type $options: array
        :param $options:
        :type $result: array
        :param $result:

    .. php:method:: testSingleHeader($headerValue, $expectedValue)

        Compare returned HTTP headers with expected values

        :type $headerValue: string
        :param $headerValue:
        :type $expectedValue: string
        :param $expectedValue:
        :returns: bool

    .. php:method:: simulate(Node $node, Application $application, Deployment $deployment, $options = [])

        Simulate this task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: executeLocalCurlRequest($url, $timeout = null, $port = null, $method = 'GET', $username = null, $password = null, $data = '', $proxy = null, $proxyPort = null)

        :type $url: string
        :param $url: Request URL
        :type $timeout: int
        :param $timeout: Request HTTP timeout, defaults to 0 (no timeout)
        :type $port: int
        :param $port: Request HTTP port
        :type $method: string
        :param $method: Request method, defaults to GET. POST, PUT and DELETE are also supported.
        :type $username: string
        :param $username: Optional username for HTTP authentication
        :type $password: string
        :param $password: Optional password for HTTP authentication
        :type $data: string
        :param $data:
        :type $proxy: string
        :param $proxy:
        :type $proxyPort: int
        :param $proxyPort:
        :returns: array time in seconds and status information im associative arrays

    .. php:method:: executeRemoteCurlRequest($url, Node $node, Deployment $deployment, $additionalCurlParameters = '')

        :type $url: string
        :param $url: Request URL
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $additionalCurlParameters: string
        :param $additionalCurlParameters:
        :returns: array time in seconds and status information im associative arrays

    .. php:method:: extractResponseHeaders($headerText)

        Split response into headers and body part

        :type $headerText: string
        :param $headerText:
        :returns: array Extracted response headers as associative array

    .. php:method:: setShellCommandService(ShellCommandService $shellCommandService)

        :type $shellCommandService: ShellCommandService
        :param $shellCommandService:

    .. php:method:: rollback(Node $node, Application $application, Deployment $deployment, $options = [])

        Rollback this task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: configureOptions($options = [])

        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:
