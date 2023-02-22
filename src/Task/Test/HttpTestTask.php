<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Test;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\HttpResponse;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\TaskExecutionException;
use function GuzzleHttp\headers_from_lines;

/**
 * A task for testing HTTP request
 *
 * This task could be used to do smoke-tests against web applications in release (e.g. on a virtual host mounted
 * on the "next" symlink).
 */
class HttpTestTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $options = $this->configureOptions($options);

        $this->logger->debug(sprintf('Requesting Url %s', $options['url']));

        if ($options['remote']) {
            $response = $this->executeRemoteCurlRequest(
                $options['url'],
                $node,
                $deployment,
                $options['additionalCurlParameters']
            );
        } else {
            $response = $this->executeLocalCurlRequest($options['url'], $options);
        }

        if ($options['expectedStatus'] !== null) {
            $this->assertExpectedStatus((int)$options['expectedStatus'], $response->getStatusCode());
        }
        if ($options['expectedHeaders'] !== null) {
            $this->assertExpectedHeaders($this->extractHeadersFromMultiLineString($options['expectedHeaders']), $response->getHeaders());
        }
        if ($options['expectedRegexp'] !== null) {
            $this->assertExpectedRegexp(explode(chr(10), $options['expectedRegexp']), $response->getBody());
        }
    }

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('url');
        $resolver->setDefaults([
            'remote' => false,
            'additionalCurlParameters' => '',
            'expectedStatus' => null,
            'expectedHeaders' => null,
            'expectedRegexp' => null,
            'timeout' => null,
            'port' => null,
            'method' => 'GET',
            'username' => null,
            'password' => null,
            'data' => null,
            'proxy' => null,
            'proxyPort' => null,
        ]);
        $resolver->setNormalizer('remote', static fn (Options $options, $value): bool => (bool)$value);
    }

    /**
     * @throws TaskExecutionException
     */
    protected function assertExpectedStatus(int $expected, int $actual): void
    {
        if ($actual !== $expected) {
            throw new TaskExecutionException(sprintf('Expected status code %d but got %d', $expected, $actual), 1319536619);
        }
    }

    protected function assertExpectedHeaders(array $expected, array $actual): void
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

    protected function assertExpectedRegexp(array $expectedRegexp, string $responseBody): void
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
     */
    protected function testSingleHeader(string $headerValue, string $expectedValue): bool
    {
        if (! $headerValue) {
            return false;
        }
        if (trim($headerValue) === '') {
            return false;
        }
        if (strpos($expectedValue, '=') === 0) {
            // = Value equals
            $result = $headerValue === trim(substr($expectedValue, 1));
        } elseif (strpos($expectedValue, '<') === 0) {
            // < Intval smaller than
            $result = (int)$headerValue < (int)substr($expectedValue, 1);
        } elseif (strpos($expectedValue, '>') === 0) {
            // > Intval bigger than
            $result = (int)$headerValue > (int)substr($expectedValue, 1);
        } else {
            $result = $headerValue === $expectedValue;
        }

        return $result;
    }

    /**
     * @throws TaskExecutionException
     * @throws GuzzleException
     */
    protected function executeLocalCurlRequest(string $url, array $options = []): HttpResponse
    {
        $guzzleOptions = [];

        if ($options['username'] !== null && $options['password'] !== null) {
            $guzzleOptions['auth'] = [$options['username'], $options['password']];
        }

        if ($options['timeout'] !== null) {
            $guzzleOptions['timeout'] = (int)ceil($options['timeout'] / 1000);
        }

        if ($options['port'] !== null) {
            $guzzleOptions['port'] = (int)$options['port'];
        }

        if ($options['proxy'] !== null && $options['proxyPort'] !== null) {
            $guzzleOptions['proxy'] = sprintf('%s:%d', $options['proxy'], $options['proxyPort']);
        }

        if ($options['data'] !== null && $options['data'] !== '') {
            $guzzleOptions['body'] = $options['data'];
            $guzzleOptions['headers'] = [
                'Content-Length' => strlen($options['data']),
            ];
        }

        try {
            $response = $this->client->request($options['method'], $url, $guzzleOptions);

            return new HttpResponse($response->getBody()->getContents(), $response->getHeaders(), $response->getStatusCode());
        } catch (RequestException $e) {
            throw new TaskExecutionException('HTTP request did not return a response', 1334347427);
        }
    }

    /**
     * @throws TaskExecutionException
     */
    protected function executeRemoteCurlRequest(
        string $url,
        Node $node,
        Deployment $deployment,
        string $additionalCurlParameters = ''
    ): HttpResponse {
        $command = 'curl -s -I ' . $additionalCurlParameters . ' ' . escapeshellarg($url);
        $head = $this->shell->execute($command, $node, $deployment, false, false);

        $command = 'curl -s ' . $additionalCurlParameters . ' ' . escapeshellarg($url);
        $body = $this->shell->execute($command, $node, $deployment, false, false);
        [$status, $headersString] = explode(chr(10), $head, 2);
        $statusParts = explode(' ', $status);
        $headers = $this->extractHeadersFromMultiLineString(trim($headersString));

        return new HttpResponse($body, $headers, (int)$statusParts[1]);
    }

    /**
     * Split response into headers and body part
     */
    protected function extractHeadersFromMultiLineString(string $headerText): array
    {
        return $headerText !== '' ? headers_from_lines(explode(chr(10), $headerText)) : [];
    }
}
