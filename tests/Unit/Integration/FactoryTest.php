<?php

declare(strict_types=1);

namespace TYPO3\Surf\Tests\Unit\Integration;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Domain\Model\FailedDeployment;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Integration\Factory;
use TYPO3\Surf\Tests\Unit\KernelAwareTrait;

class FactoryTest extends TestCase
{
    use KernelAwareTrait;

    /**
     * @var Factory
     */
    protected $subject;

    /**
     * @var FilesystemInterface|ObjectProphecy
     */
    protected $filesystem;

    /**
     * @var bool
     */
    protected $preserveGlobalState = false;

    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * @var Logger|ObjectProphecy
     */
    private $logger;

    protected function setUp(): void
    {
        $this->filesystem = $this->prophesize(FilesystemInterface::class);

        $this->logger = $this->prophesize(Logger::class);

        $this->subject = new Factory($this->filesystem->reveal(), $this->logger->reveal());
        $this->subject->setContainer(static::getKernel()->getContainer());
    }

    /**
     * @test
     */
    public function getDeploymentsBasePath(): void
    {
        $expectedDeploymentPath = '/var/www/html/.surf';

        $this->filesystem->getRealPath('./.surf')->willReturn($expectedDeploymentPath);
        $this->filesystem->isDirectory($expectedDeploymentPath)->willReturn(true);
        $this->filesystem->fileExists($expectedDeploymentPath)->willReturn(true);

        self::assertSame($expectedDeploymentPath, $this->subject->getDeploymentsBasePath());
    }

    /**
     * @test
     */
    public function getDeploymentsBasePathThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $expectedDeploymentPath = '/var/www/html/.surf';

        $this->filesystem->getRealPath('./.surf')->willReturn($expectedDeploymentPath);
        $this->filesystem->isDirectory($expectedDeploymentPath)->willReturn(true, false);
        $this->filesystem->fileExists($expectedDeploymentPath)->willReturn(false);
        $this->filesystem->createDirectory($expectedDeploymentPath)->willReturn(false);

        $this->subject->getDeploymentsBasePath();
    }

    /**
     * @test
     */
    public function getDeploymentsBasePathFromGivenPath(): void
    {
        $expectedDeploymentPath = '/var/www/html/.surf';

        $this->filesystem->getRealPath('./.surf')->willReturn($expectedDeploymentPath);
        $this->filesystem->fileExists($expectedDeploymentPath)->willReturn(true);

        self::assertSame($expectedDeploymentPath, $this->subject->getDeploymentsBasePath($expectedDeploymentPath));
    }

    /**
     * @test
     */
    public function getDeploymentsBasePathFromDefinedSurfHomeDirectory(): void
    {
        putenv('SURF_HOME=foo');

        $this->filesystem->getRealPath('./.surf')->willReturn('foo');
        $this->filesystem->isDirectory('foo')->willReturn(false);
        $this->filesystem->fileExists('foo')->willReturn(true);
        $this->filesystem->fileExists('foo/deployments')->willReturn(true);

        self::assertSame('foo/deployments', $this->subject->getDeploymentsBasePath());
    }

    /**
     * @test
     */
    public function getDeploymentsBasePathFromHomeDirectory(): void
    {
        putenv('HOME=foo');

        $this->filesystem->getRealPath('./.surf')->willReturn('foo');
        $this->filesystem->isDirectory('foo')->willReturn(false);
        $this->filesystem->fileExists('foo/.surf')->willReturn(true);
        $this->filesystem->fileExists('foo/.surf/deployments')->willReturn(true);

        self::assertSame('foo/.surf/deployments', $this->subject->getDeploymentsBasePath());
    }

    /**
     * @test
     */
    public function getDeploymentsBasePathFromThrowsExceptionNoHomeEnvironmentVariableDefined(): void
    {
        $this->expectException(RuntimeException::class);

        putenv('HOME');

        $this->filesystem->getRealPath('./.surf')->willReturn('foo');
        $this->filesystem->isDirectory('foo')->willReturn(false);

        $this->subject->getDeploymentsBasePath();
    }

    /**
     * @test
     */
    public function getDeploymentsBasePathFromThrowsExceptionNoAppDataEnvironmentVariableDefined(): void
    {
        $this->expectException(RuntimeException::class);

        define('PHP_WINDOWS_VERSION_MAJOR', 'foo');

        $this->filesystem->getRealPath('./.surf')->willReturn('foo');
        $this->filesystem->isDirectory('foo')->willReturn(false);

        $this->subject->getDeploymentsBasePath();
    }

    /**
     * @test
     */
    public function getDeploymentsBasePathFromAppDataEnvironmentVariable(): void
    {
        define('PHP_WINDOWS_VERSION_MAJOR', 'foo');
        putenv('APPDATA=foo');

        $this->filesystem->getRealPath('./.surf')->willReturn('foo');
        $this->filesystem->isDirectory('foo')->willReturn(false);
        $this->filesystem->fileExists('foo/Surf')->willReturn(true);
        $this->filesystem->fileExists('foo/Surf/deployments')->willReturn(true);

        self::assertSame('foo/Surf/deployments', $this->subject->getDeploymentsBasePath());
    }

    /**
     * @test
     */
    public function getDeploymentNames(): void
    {
        $expectedDeploymentPath = '/var/www/html/.surf';

        $this->filesystem->getRealPath('./.surf')->willReturn($expectedDeploymentPath);
        $this->filesystem->isDirectory($expectedDeploymentPath)->willReturn(true);
        $this->filesystem->fileExists($expectedDeploymentPath)->willReturn(true);

        $files = [$expectedDeploymentPath . '/deployment.php'];

        $this->filesystem->glob($expectedDeploymentPath . '/*.php')->willReturn($files);

        $deploymentNames = $this->subject->getDeploymentNames();

        self::assertCount(1, $deploymentNames);
        self::assertContains('deployment', $deploymentNames);
    }

    /**
     * @test
     */
    public function getWorkspacesBasePathFromSurfWorkspaceEnvironmentVariable(): void
    {
        putenv('SURF_WORKSPACE=.surf');

        $this->filesystem->fileExists('.surf')->willReturn(true);

        self::assertSame('.surf', $this->subject->getWorkspacesBasePath());
    }

    /**
     * @test
     */
    public function getWorkspacesBasePathFromPath(): void
    {
        $this->filesystem->fileExists('/var/www/html/workspace')->willReturn(true);

        self::assertSame('/var/www/html/workspace', $this->subject->getWorkspacesBasePath('/var/www/html/'));
    }

    /**
     * @test
     */
    public function getWorkspacesBasePathFromPathWithDefinedConstant(): void
    {
        define('PHP_WINDOWS_VERSION_MAJOR', 'foo');

        $this->filesystem->fileExists('/var/www/html/workspace')->willReturn(true);

        self::assertSame('/var/www/html/workspace', $this->subject->getWorkspacesBasePath('/var/www/html/'));
    }

    /**
     * @test
     */
    public function getWorkspacesBasePathFromPathWithDefinedConstantAndLocalAppDataEnvironmentVariable(): void
    {
        putenv('LOCALAPPDATA=/var/www/html/');
        define('PHP_WINDOWS_VERSION_MAJOR', 'foo');

        $this->filesystem->fileExists('/var/www/html/Surf')->willReturn(true);

        self::assertSame('/var/www/html/Surf', $this->subject->getWorkspacesBasePath('/var/www/html/'));
    }

    /**
     * @test
     */
    public function getDeployment(): void
    {
        putenv('HOME=' . __DIR__ . '/Fixtures');

        $files = [getenv('HOME') . '/.surf/deployments/deploy.php'];

        $this->filesystem->getRealPath('./.surf')->willReturn('foo');
        $this->filesystem->isDirectory('foo')->willReturn(false);
        $this->filesystem->fileExists(Argument::any())->willReturn(true);

        $deployment = $this->subject->getDeployment('deploy');

        self::assertFalse($deployment->getForceRun());
        self::assertTrue($deployment->isInitialized());
    }

    /**
     * @test
     */
    public function getDeploymentWithoutSimulation(): void
    {
        putenv('HOME=' . __DIR__ . '/Fixtures');

        $files = [getenv('HOME') . '/.surf/deployments/deploy.php'];

        $this->filesystem->getRealPath('./.surf')->willReturn('foo');
        $this->filesystem->isDirectory('foo')->willReturn(false);
        $this->filesystem->fileExists(Argument::any())->willReturn(true);

        $deployment = $this->subject->getDeployment('deploy', null, false);

        $this->logger->pushHandler(new StreamHandler(getenv('HOME') . '/.surf/workspace/logs/deploy.log'))->shouldBeCalledOnce();

        self::assertFalse($deployment->getForceRun());
        self::assertTrue($deployment->isInitialized());
    }

    /**
     * @test
     */
    public function getFirstAndOnlyDeployment(): void
    {
        putenv('HOME=' . __DIR__ . '/Fixtures');

        $files = [getenv('HOME') . '/.surf/deployments/deploy.php'];

        $this->filesystem->glob(getenv('HOME') . '/.surf/deployments/*.php')->willReturn($files);
        $this->filesystem->getRealPath('./.surf')->willReturn('foo');
        $this->filesystem->isDirectory('foo')->willReturn(false);
        $this->filesystem->fileExists(Argument::any())->willReturn(true);

        $this->subject->getDeployment('');
    }

    /**
     * @test
     */
    public function getDeploymentImplicitlyThrowsException(): void
    {
        putenv('HOME=' . __DIR__ . '/Fixtures');

        $this->expectException(InvalidConfigurationException::class);

        $files = [
            getenv('HOME') . '/.surf/deployments/deploy.php',
            getenv('HOME') . '/.surf/deployments/bar.php',
        ];
        $this->filesystem->glob(getenv('HOME') . '/.surf/deployments/*.php')->willReturn($files);
        $this->filesystem->getRealPath('./.surf')->willReturn('foo');
        $this->filesystem->isDirectory('foo')->willReturn(false);
        $this->filesystem->fileExists(Argument::any())->willReturn(true);

        $this->subject->getDeployment('');
    }

    /**
     * @test
     */
    public function getFailedDeployment(): void
    {
        putenv('HOME=' . __DIR__ . '/Fixtures');

        $this->filesystem->getRealPath('./.surf')->willReturn('foo');
        $this->filesystem->isDirectory('foo')->willReturn(true);
        $this->filesystem->fileExists('foo')->willReturn(true);
        $this->filesystem->fileExists(getenv('HOME') . '/.surf')->willReturn(true);
        $this->filesystem->fileExists(getenv('HOME') . '/.surf/workspace')->willReturn(true);
        $this->filesystem->fileExists('foo/foo.php')->willReturn(false);

        $deploymentFile = getenv('HOME') . '/.surf/deployments/foo.php';

        $this->filesystem->fileExists($deploymentFile)->willReturn(false);

        $this->logger->error(sprintf("The deployment file %s does not exist.\n", $deploymentFile))->shouldBeCalledOnce();

        $deployment = $this->subject->getDeployment('foo');

        self::assertSame($this->logger->reveal(), $deployment->getLogger());
        self::assertInstanceOf(FailedDeployment::class, $deployment);
    }
}
