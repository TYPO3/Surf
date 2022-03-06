<?php

declare(strict_types=1);

namespace TYPO3\Surf\Tests\Unit\Task\Test;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;
use TYPO3\Surf\Task\Test\HttpTestTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class HttpTestTaskTest extends BaseTaskTest
{
    /**
     * @var HttpTestTask
     */
    protected $task;

    private const URL = 'https://whatever.iwant.com';

    /**
     * @test
     */
    public function executeRemoteCurlCommand(): void
    {
        $options = [
            'url' => self::URL,
            'remote' => 1,
            'expectedStatus' => 200,
            'expectedHeaders' => 'X-Powered-By:PHP/5.6.34
            Server:Apache',
            'expectedRegexp' => '/Hello/',
        ];

        $this->responses = [
            sprintf('curl -s -I  %s', escapeshellarg(self::URL)) => 'HTTP/1.1 200 OK
Date: Fri, 09 Nov 2018 12:28:21 GMT
Server: Apache
X-Powered-By: PHP/5.6.34
Upgrade: h2c
Connection: Upgrade
Content-Type: text/html; charset=UTF-8',
            sprintf('curl -s  %s', escapeshellarg(self::URL)) => 'Hello World',
        ];
        $this->assertNoExceptionThrown($options);
    }

    /**
     * @test
     */
    public function emptyUrlOptionThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function correctStatusCodeIsReturned(): void
    {
        $options = [
            'url' => self::URL,
            'expectedStatus' => 200,
        ];
        $this->mockClient(new Response(200));
        $this->assertNoExceptionThrown($options);
    }

    /**
     * @test
     */
    public function inCorrectStatusCodeThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $options = [
            'url' => self::URL,
            'expectedStatus' => 300,
        ];
        $this->mockClient(new Response(200, [], 'Hello World'));
        $this->expectException(TaskExecutionException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @test
     */
    public function correctResponseHeaders(): void
    {
        $options = [
            'url' => self::URL,
            'expectedHeaders' => 'X-Powered-By:PHP/5.6.34
            Server:Apache',
        ];
        $this->mockClient(new Response(200, ['Server' => 'Apache', 'X-Powered-By' => 'PHP/5.6.34'], 'Hello World'));
        $this->assertNoExceptionThrown($options);
    }

    /**
     * @test
     */
    public function inCorrectResponseHeadersThrowsException(): void
    {
        $options = [
            'url' => self::URL,
            'expectedHeaders' => 'Server:SomeWeirdServer',
        ];
        $this->mockClient(new Response(200, ['Server:Apache'], 'Hello World'));
        $this->expectException(TaskExecutionException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @test
     */
    public function responseBodyContainsCorrectContent(): void
    {
        $options = [
            'url' => self::URL,
            'expectedRegexp' => '/Hello/',
        ];
        $this->mockClient(new Response(200, [], 'Hello World'));
        $this->assertNoExceptionThrown($options);
    }

    /**
     * @test
     */
    public function responseBodyDoesNotContainsCorrectContent(): void
    {
        $options = [
            'url' => self::URL,
            'expectedRegexp' => '/Some stupid content here/',
        ];
        $this->mockClient(new Response(200, [], 'Hello World'));
        $this->expectException(TaskExecutionException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @return HttpTestTask
     */
    protected function createTask(): HttpTestTask
    {
        return new HttpTestTask(new Client());
    }

    protected function assertNoExceptionThrown(array $options): void
    {
        $exception = null;

        try {
            $this->task->execute($this->node, $this->application, $this->deployment, $options);
        } catch (Exception|GuzzleException $e) {
            $exception = $e;
        }

        self::assertNull($exception);
    }

    protected function mockClient(Response $response): void
    {
        // Create a mock and queue one response.
        $mock = new MockHandler([$response]);

        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler]);
        $this->task->setClient($client);
    }
}
