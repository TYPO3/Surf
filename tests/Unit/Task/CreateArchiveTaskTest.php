<?php

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Domain\Generator\IdGeneratorInterface;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\CreateArchiveTask;

class CreateArchiveTaskTest extends BaseTaskTest
{
    private const SOURCE_DIRECTORY = '/var/www/html/source/';

    /**
     * @var FilesystemInterface|ObjectProphecy
     */
    private $filesystem;

    /**
     * @var IdGeneratorInterface|ObjectProphecy
     */
    private $idGenerator;

    /**
     * @test
     */
    public function executeWithoutSourceDirectoryThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment, []);
    }

    /**
     * @test
     */
    public function executeWitWrongSourceDirectoryOptionThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->filesystem->isDirectory(self::SOURCE_DIRECTORY)->willReturn(false);
        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['sourceDirectory' => self::SOURCE_DIRECTORY]
        );
    }

    /**
     * @test
     */
    public function executeWithoutTargetFileThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->filesystem->isDirectory(self::SOURCE_DIRECTORY)->willReturn(true);
        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['sourceDirectory' => self::SOURCE_DIRECTORY]
        );
    }

    /**
     * @test
     */
    public function executeWithWrongTargetFileEndingThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->filesystem->isDirectory(self::SOURCE_DIRECTORY)->willReturn(true);
        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['sourceDirectory' => self::SOURCE_DIRECTORY, 'targetFile' => 'file.txt']
        );
    }

    /**
     * @test
     */
    public function executeWithoutBaseDirectoryThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->filesystem->isDirectory(self::SOURCE_DIRECTORY)->willReturn(true);
        $this->task->execute(
            $this->node,
            $this->application,
            $this->deployment,
            ['sourceDirectory' => self::SOURCE_DIRECTORY, 'targetFile' => 'file.zip']
        );
    }

    /**
     * @test
     */
    public function executeSuccessfullyForTarGz(): void
    {
        $options = [
            'sourceDirectory' => self::SOURCE_DIRECTORY,
            'targetFile' => 'file.tar.gz',
            'baseDirectory' => self::SOURCE_DIRECTORY
        ];
        $this->filesystem->isDirectory(self::SOURCE_DIRECTORY)->willReturn(true);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $sourcePath = $this->deployment->getApplicationReleasePath($this->node);
        $tarOptions = sprintf(' --transform="s,^%s,%s," ', ltrim($sourcePath, '/'), $options['baseDirectory']);
        $tarOptions .= sprintf(' -czf %s %s', $options['targetFile'], $sourcePath);

        $expectedCommands = [
            sprintf('rm -f %1$s; mkdir -p ' . dirname('%1$s'), $options['targetFile']),
            sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions)
        ];

        foreach ($expectedCommands as $command) {
            $this->assertCommandExecuted($command);
        }
    }

    /**
     * @test
     */
    public function executeSuccessfullyForZip(): void
    {
        $options = [
            'exclude' => ['foo'],
            'sourceDirectory' => self::SOURCE_DIRECTORY,
            'targetFile' => 'file.zip',
            'baseDirectory' => self::SOURCE_DIRECTORY
        ];
        $this->filesystem->isDirectory(self::SOURCE_DIRECTORY)->willReturn(true);
        $this->filesystem->getTemporaryDirectory()->willReturn('tmp');
        $this->idGenerator->generate('f3_deploy')->willReturn('12345');
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $sourcePath = $this->deployment->getApplicationReleasePath($this->node);
        $tarOptions = sprintf(' --transform="s,^%s,%s," ', ltrim($sourcePath, '/'), $options['baseDirectory']);
        foreach ($options['exclude'] as $excludePattern) {
            $tarOptions .= sprintf(' --exclude="%s" ', $excludePattern);
        }
        $tarOptions .= sprintf(' -cf %s/out.tar %s', 'tmp/12345', $sourcePath);

        $expectedCommands = [
            sprintf('rm -f %1$s; mkdir -p ' . dirname('%1$s'), $options['targetFile']),
            sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions),
            'mkdir -p tmp/12345',
            sprintf(
                'cd %s; tar -xf out.tar; rm out.tar; zip --quiet -9 -r out %s',
                'tmp/12345',
                $options['baseDirectory']
            ),
            sprintf('mv %s/out.zip %s; rm -Rf %s', 'tmp/12345', $options['targetFile'], 'tmp/12345')
        ];

        foreach ($expectedCommands as $command) {
            $this->assertCommandExecuted($command);
        }
    }

    /**
     * @test
     */
    public function executeSuccessfullyForTarBz2(): void
    {
        $options = [
            'sourceDirectory' => self::SOURCE_DIRECTORY,
            'targetFile' => 'file.tar.bz2',
            'baseDirectory' => self::SOURCE_DIRECTORY
        ];
        $this->filesystem->isDirectory(self::SOURCE_DIRECTORY)->willReturn(true);
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $sourcePath = $this->deployment->getApplicationReleasePath($this->node);
        $tarOptions = sprintf(' --transform="s,^%s,%s," ', ltrim($sourcePath, '/'), $options['baseDirectory']);
        $tarOptions .= sprintf(' -cjf %s %s', $options['targetFile'], $sourcePath);

        $expectedCommands = [
            sprintf('rm -f %1$s; mkdir -p ' . dirname('%1$s'), $options['targetFile']),
            sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions)
        ];

        foreach ($expectedCommands as $command) {
            $this->assertCommandExecuted($command);
        }
    }

    /**
     * @return CreateArchiveTask
     */
    protected function createTask()
    {
        $this->filesystem = $this->prophesize(FilesystemInterface::class);
        $this->idGenerator = $this->prophesize(IdGeneratorInterface::class);
        return new CreateArchiveTask($this->filesystem->reveal(), $this->idGenerator->reveal());
    }
}
