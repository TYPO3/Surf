<?php

namespace TYPO3\Surf\Task\Test;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\HttpResponse;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * A task for testing HTTP request
 *
 * This task could be used to do smoke-tests against web applications in release (e.g. on a virtual host mounted
 * on the "next" symlink).
 */
class HttpTestTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * HttpTestTask constructor.
     *
     * @param ClientInterface|null $client
     */
    public function __construct(ClientInterface $client = null)
    {
        if (! $client instanceof ClientInterface) {
            $client = new Client();
        }
        $this->client = $client;
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     *
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (! isset($options['url'])) {
            throw new InvalidConfigurationException('No url option provided for HttpTestTask', 1319534939);
        }

        $deployment->getLogger()->debug(sprintf('Requesting Url %s', $options['url']));

        if (isset($options['remote']) && (bool)$options['remote']) {
            $response = $this->executeRemoteCurlRequest(
                $options['url'],
                $node,
                $deployment,
                isset($options['additionalCurlParameters']) ? $options['additionalCurlParameters'] : ''
            );
        } else {
            $response = $this->executeLocalCurlRequest($options['url'], $options);
        }

        if (isset($options['expectedStatus'])) {
            $this->assertExpectedStatus($options['expectedStatus'], $response->getStatusCode());
        }
        if (isset($options['expectedHeaders'])) {
            $this->assertExpectedHeaders($this->extractHeadersFromMultiLineString($options['expectedHeaders']), $response->getHeaders());
        }
        if (isset($options['expectedRegexp'])) {
            $this->assertExpectedRegexp(explode(chr(10), $options['expectedRegexp']), $response->getBody());
        }
    }

    /**
     * @param int $expected
     * @param int $actual
     *
     * @throws TaskExecutionException
     */
    protected function assertExpectedStatus($expected, $actual)
    {
        if ((int)$actual !== (int)$expected) {
            throw new TaskExecutionException(sprintf('Expected status code %d but got %d', $expected, $actual), 1319536619);
        }
    }

    /**
     * @param array $expected
     * @param array $actual
     *
     * @throws TaskExecutionException
     */
    protected function assertExpectedHeaders(array $expected, array $actual)
    {
        if (count($expected) > 0) {
            foreach ($expected as $headerName => $expectedValue) {
                if (! isset($actual[$headerName])) {
                    throw new TaskExecutionException('Expected header "' . $headerName . '" not present', 1319535441);
                }
                $headerValue = $actual[$headerName];

                if (is_array($headerValue)) {
                    $headerValue = array_shift($headerValue);
                }

                if (is_array($expectedValue)) {
                    $expectedValue = array_shift($expectedValue);
                }

                $partialSuccess = $this->testSingleHeader($headerValue, $expectedValue);
                if (! $partialSuccess) {
                    throw new TaskExecutionException(sprintf('Expected header value for "%s" did not match "%s": "%s"', $headerName, $expectedValue, $headerValue), 1319535733);
                }
            }
        }
    }

    /**
     * @param array $expectedRegexp
     * @param string $responseBody
     *
     * @throws TaskExecutionException
     */
    protected function assertExpectedRegexp(array $expectedRegexp, $responseBody)
    {
        if (count($expectedRegexp) > 0) {
            foreach ($expectedRegexp as $regexp) {
                $regexp = trim($regexp);
                if ($regexp !== '' && ! preg_match($regexp, $responseBody)) {
                    throw new TaskExecutionException('Body did not match expected regular expression "' . $regexp . '": ' . substr($responseBody, 0, 200) . (strlen($responseBody) > 200 ? '...' : ''), 1319536046);
                }
            }
        }
    }

    /**
     * Compare returned HTTP headers with expected values
     *
     * @param string $headerValue
     * @param string $expectedValue
     *
     * @return bool
     */
    protected function testSingleHeader($headerValue, $expectedValue)
    {
        if (! $headerValue || trim($headerValue) === '') {
            return false;
        }

        // = Value equals
        if (strpos($expectedValue, '=') === 0) {
            $result = $headerValue === trim(substr($expectedValue, 1));
        } // < Intval smaller than
        elseif (strpos($expectedValue, '<') === 0) {
            $result = (int)$headerValue < (int)substr($expectedValue, 1);
        } // > Intval bigger than
        elseif (strpos($expectedValue, '>') === 0) {
            $result = (int)$headerValue > (int)substr($expectedValue, 1);
        } // Default
        else {
            $result = $headerValue === $expectedValue;
        }

        return $result;
    }

    /**
     * @param string $url Request URL
     * @param array $options
     *
     * @return HttpResponse
     * @throws TaskExecutionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function executeLocalCurlRequest($url, array $options = [])
    {
        $timeout = isset($options['timeout']) ? $options['timeout'] : null;
        $port = isset($options['port']) ? $options['port'] : null;
        $method = isset($options['method']) ? $options['method'] : 'GET';
        $username = isset($options['username']) ? $options['username'] : null;
        $password = isset($options['password']) ? $options['password'] : null;
        $data = isset($options['data']) ? $options['data'] : '';
        $proxy = isset($options['proxy']) ? $options['proxy'] : null;
        $proxyPort = isset($options['proxyPort']) ? $options['proxyPort'] : null;

        $guzzleOptions = [];

        if ($username !== null && $password !== null) {
            $guzzleOptions['auth'] = [$username, $password];
        }

        if ($timeout !== null) {
            $guzzleOptions['timeout'] = (int)ceil($timeout / 1000);
        }

        if ($port !== null) {
            $guzzleOptions['port'] = (int)$port;
        }

        if ($proxy !== null && $proxyPort !== null) {
            $guzzleOptions['proxy'] = sprintf('%s:%d', $proxy, $proxyPort);
        }

        if ($data !== null && $data !== '') {
            $guzzleOptions['body'] = $data;
            $guzzleOptions['headers'] = [
                'Content-Length' => strlen($data),
            ];
        }

        try {
            $response = $this->client->request($method, $url, $guzzleOptions);
            return new HttpResponse($response->getBody()->getContents(), $response->getHeaders(), $response->getStatusCode());
        } catch (RequestException $e) {
            throw new TaskExecutionException('HTTP request did not return a response', 1334347427);
        }
    }

    /**
     * @param string $url Request URL
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param string $additionalCurlParameters
     *
     * @return HttpResponse
     * @throws TaskExecutionException
     */
    protected function executeRemoteCurlRequest($url, Node $node, Deployment $deployment, $additionalCurlParameters = '')
    {
        $command = 'curl -s -I ' . $additionalCurlParameters . ' ' . escapeshellarg($url);
        $head = $this->shell->execute($command, $node, $deployment, false, false);

        $command = 'curl -s ' . $additionalCurlParameters . ' ' . escapeshellarg($url);
        $body = $this->shell->execute($command, $node, $deployment, false, false);
        list($status, $headersString) = explode(chr(10), $head, 2);
        $statusParts = explode(' ', $status);
        $headers = $this->extractHeadersFromMultiLineString(trim($headersString));

        return new HttpResponse($body, $headers, $statusParts[1]);
    }

    /**
     * Split response into headers and body part
     *
     * @param string $headerText
     *
     * @return array Extracted response headers as associative array
     */
    protected function extractHeadersFromMultiLineString($headerText)
    {
        return $headerText ? \GuzzleHttp\headers_from_lines(explode(chr(10), $headerText)) : [];
    }
}
