<?php

namespace TYPO3\Surf\Tests\Unit\Task\Php;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Domain\Generator\RandomBytesGeneratorInterface;
use TYPO3\Surf\Exception\TaskExecutionException;
use TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask;
use TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class WebOpcacheResetCreateScriptTaskTest extends BaseTaskTest
{
    /**
     * @var FilesystemInterface|MockObject
     */
    private $filesystem;

    /**
     * @var MockObject|RandomBytesGeneratorInterface
     */
    private $randomBytesGenerator;

    /**
     * @test
     */
    public function createScriptByRandomString(): void
    {
        $randomBytes = random_bytes(32);
        $expectedScriptIdentifier = bin2hex($randomBytes);

        $expectedScriptIdentifierPath = sprintf(
            '%s/surf-opcache-reset-%s.php',
            Files::concatenatePaths([$this->deployment->getWorkspacePath($this->application), 'public']),
            $expectedScriptIdentifier
        );
        $this->filesystem
            ->expects(self::once())
            ->method('put')
            ->with($expectedScriptIdentifierPath)
            ->willReturn(true);

        $this->randomBytesGenerator->expects(self::once())->method('generate')->willReturn($randomBytes);
        $this->task->execute($this->node, $this->application, $this->deployment);

        self::assertSame(
            $expectedScriptIdentifier,
            $this->application->getOption(WebOpcacheResetExecuteTask::class . '[scriptIdentifier]')
        );
    }

    /**
     * @test
     */
    public function createScriptInWebDirectory(): void
    {
        $this->application->setOption('webDirectory', 'public');
        $randomBytes = random_bytes(32);
        $expectedScriptIdentifier = bin2hex($randomBytes);

        $expectedScriptIdentifierPath = sprintf(
            '%s/surf-opcache-reset-%s.php',
            Files::concatenatePaths([$this->deployment->getWorkspacePath($this->application), 'public']),
            $expectedScriptIdentifier
        );
        $this->filesystem->expects(self::once())
            ->method('put')
            ->with($expectedScriptIdentifierPath)
            ->willReturn(true);

        $this->randomBytesGenerator->expects(self::once())->method('generate')->willReturn($randomBytes);
        $this->task->execute($this->node, $this->application, $this->deployment);

        self::assertSame(
            $expectedScriptIdentifier,
            $this->application->getOption(WebOpcacheResetExecuteTask::class . '[scriptIdentifier]')
        );
    }

    /**
     * @test
     */
    public function createScriptByDefinedIdentifier(): void
    {
        $scriptIdentifier = '123456';
        $expectedScriptIdentifierPath = sprintf(
            '%s/surf-opcache-reset-%s.php',
            Files::concatenatePaths([$this->deployment->getWorkspacePath($this->application), 'public']),
            $scriptIdentifier
        );
        $this->filesystem->expects(self::once())
            ->method('put')
            ->with($expectedScriptIdentifierPath)
            ->willReturn(true);

        $this->randomBytesGenerator->expects(self::never())->method('generate');

        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['scriptIdentifier' => $scriptIdentifier]
        );
    }

    /**
     * @test
     */
    public function createNothingInDryRunMode(): void
    {
        $this->deployment->setDryRun(true);
        $this->filesystem->expects(self::never())->method('put');
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     */
    public function throwExceptionIfFileCanNotBeWritten(): void
    {
        $this->expectException(TaskExecutionException::class);
        $this->filesystem->expects(self::once())->method('put')->willReturn(false);
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @return WebOpcacheResetCreateScriptTask
     */
    protected function createTask(): WebOpcacheResetCreateScriptTask
    {
        $this->filesystem = $this->getMockBuilder(FilesystemInterface::class)->getMock();
        $this->randomBytesGenerator = $this->getMockBuilder(RandomBytesGeneratorInterface::class)->getMock();

        return new WebOpcacheResetCreateScriptTask($this->randomBytesGenerator, $this->filesystem);
    }
}
