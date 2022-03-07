<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Php;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;
use TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class WebOpcacheResetExecuteTaskTest extends BaseTaskTest
{
    /**
     * @var FilesystemInterface|MockObject
     */
    private $filesystem;

    /**
     * @test
     */
    public function optionBaseUrlIsNotProvidedThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function optionScriptIdentifierIsNotProvidedThrowsException(): void
    {
        $options = [
            'baseUrl' => 'https://domain.com/',
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @test
     */
    public function resultIsNotSuccessfulThrowsException(): void
    {
        $this->filesystem->expects(self::once())->method('get')->willReturn('failure');

        $options = [
            'baseUrl' => 'https://domain.com/',
            'scriptIdentifier' => 'script-identifier',
            'throwErrorOnWebOpCacheResetExecuteTask' => true,
        ];

        $this->expectException(TaskExecutionException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @test
     */
    public function resultIsSuccessfulWithoutStreamContext(): void
    {
        $this->filesystem
            ->expects(self::once())
            ->method('get')
            ->with('https://domain.com/surf-opcache-reset-script-identifier.php', false, null)
            ->willReturn('success');

        $options = [
            'baseUrl' => 'https://domain.com/',
            'scriptIdentifier' => 'script-identifier',
            'throwErrorOnWebOpCacheResetExecuteTask' => true,
            'stream_context' => '',
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @test
     */
    public function resultIsSuccessfulWithStreamContext(): void
    {
        $this->filesystem
            ->expects(self::once())
            ->method('get')
            ->with('https://domain.com/surf-opcache-reset-script-identifier.php', false)
            ->willReturn('success');

        $options = [
            'baseUrl' => 'https://domain.com/',
            'scriptIdentifier' => 'script-identifier',
            'throwErrorOnWebOpCacheResetExecuteTask' => true,
            'stream_context' => [
                'http' => [
                    'header' => 'Authorization: Basic ' . base64_encode('username:password'),
                ],
            ],
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @return WebOpcacheResetExecuteTask
     */
    protected function createTask(): WebOpcacheResetExecuteTask
    {
        $this->filesystem = $this->getMockBuilder(FilesystemInterface::class)->getMock();

        return new WebOpcacheResetExecuteTask($this->filesystem);
    }
}
