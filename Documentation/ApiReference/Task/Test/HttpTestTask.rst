-------------------------------------
TYPO3\\Surf\\Task\\Test\\HttpTestTask
-------------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Test

.. php:class:: HttpTestTask

    A task for testing HTTP request

    This task could be used to do smoke-tests against web applications in release (e.g. on a virtual host mounted on the "next" symlink).

    .. php:attr:: shell

        protected ShellCommandService

    .. php:method:: __construct(ClientInterface $client = null)

        HttpTestTask constructor.

        :type $client: ClientInterface
        :param $client:

    .. php:method:: setClient(ClientInterface $client)

        :type $client: ClientInterface
        :param $client:

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

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:

    .. php:method:: assertExpectedStatus($expected, $actual)

        :type $expected: int
        :param $expected:
        :type $actual: int
        :param $actual:

    .. php:method:: assertExpectedHeaders($expected, $actual)

        :type $expected: array
        :param $expected:
        :type $actual: array
        :param $actual:

    .. php:method:: assertExpectedRegexp($expectedRegexp, $responseBody)

        :type $expectedRegexp: array
        :param $expectedRegexp:
        :type $responseBody: string
        :param $responseBody:

    .. php:method:: testSingleHeader($headerValue, $expectedValue)

        Compare returned HTTP headers with expected values

        :type $headerValue: string
        :param $headerValue:
        :type $expectedValue: string
        :param $expectedValue:
        :returns: bool

    .. php:method:: executeLocalCurlRequest($url, $options = [])

        :type $url: string
        :param $url: Request URL
        :type $options: array
        :param $options:
        :returns: HttpResponse

    .. php:method:: executeRemoteCurlRequest($url, Node $node, Deployment $deployment, $additionalCurlParameters = '')

        :type $url: string
        :param $url: Request URL
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $additionalCurlParameters: string
        :param $additionalCurlParameters:
        :returns: HttpResponse

    .. php:method:: extractHeadersFromMultiLineString($headerText)

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

    .. php:method:: simulate(Node $node, Application $application, Deployment $deployment, $options = [])

        Simulate this task (e.g. by logging commands it would execute)

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
