<?php

namespace TYPO3\Surf\Tests\Unit\Task\Php;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

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
     * @var FilesystemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RandomBytesGeneratorInterface
     */
    private $randomBytesGenerator;

    /**
     * @test
     */
    public function createScriptByRandomString()
    {
        $randomBytes = random_bytes(32);
        $expectedScriptIdentifier = bin2hex($randomBytes);

        $expectedScriptIdentifierPath = sprintf('%s/surf-opcache-reset-%s.php', Files::concatenatePaths([$this->deployment->getWorkspacePath($this->application), 'Web']), $expectedScriptIdentifier);
        $this->filesystem->expects($this->once())->method('put')->with($expectedScriptIdentifierPath)->willReturn(true);
        $this->randomBytesGenerator->expects($this->once())->method('generate')->willReturn($randomBytes);
        $this->task->execute($this->node, $this->application, $this->deployment);

        $this->assertSame($expectedScriptIdentifier, $this->application->getOption(WebOpcacheResetExecuteTask::class . '[scriptIdentifier]'));
    }

    /**
     * @test
     */
    public function createScriptInWebDirectory()
    {
        $this->application->setOption('webDirectory', 'public');
        $randomBytes = random_bytes(32);
        $expectedScriptIdentifier = bin2hex($randomBytes);

        $expectedScriptIdentifierPath = sprintf('%s/surf-opcache-reset-%s.php', Files::concatenatePaths([$this->deployment->getWorkspacePath($this->application), 'public']), $expectedScriptIdentifier);
        $this->filesystem->expects($this->once())->method('put')->with($expectedScriptIdentifierPath)->willReturn(true);
        $this->randomBytesGenerator->expects($this->once())->method('generate')->willReturn($randomBytes);
        $this->task->execute($this->node, $this->application, $this->deployment);

        $this->assertSame($expectedScriptIdentifier, $this->application->getOption(WebOpcacheResetExecuteTask::class . '[scriptIdentifier]'));
    }

    /**
     * @test
     */
    public function createScriptByDefinedIdentifier()
    {
        $scriptIdentifier = '123456';
        $expectedScriptIdentifierPath = sprintf('%s/surf-opcache-reset-%s.php', Files::concatenatePaths([$this->deployment->getWorkspacePath($this->application), 'Web']), $scriptIdentifier);
        $this->filesystem->expects($this->once())->method('put')->with($expectedScriptIdentifierPath)->willReturn(true);
        $this->randomBytesGenerator->expects($this->never())->method('generate');
        $this->task->execute($this->node, $this->application, $this->deployment, ['scriptIdentifier' => $scriptIdentifier]);
    }

    /**
     * @test
     */
    public function createNothingInDryRunMode()
    {
        $this->deployment->setDryRun(true);
        $this->filesystem->expects($this->never())->method('put');
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     */
    public function throwExceptionIfFileCanNotBeWritten()
    {
        $this->expectException(TaskExecutionException::class);
        $this->filesystem->expects($this->once())->method('put')->willReturn(false);
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @return WebOpcacheResetCreateScriptTask
     */
    protected function createTask()
    {
        $this->filesystem = $this->getMockBuilder(FilesystemInterface::class)->getMock();
        $this->randomBytesGenerator = $this->getMockBuilder(RandomBytesGeneratorInterface::class)->getMock();

        return new WebOpcacheResetCreateScriptTask($this->randomBytesGenerator, $this->filesystem);
    }
}
