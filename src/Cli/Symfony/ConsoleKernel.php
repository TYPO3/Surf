<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Cli\Symfony;

use Phar;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\Surf\Cli\Symfony\CompilerPasses\CommandsToApplicationCompilerPass;
use TYPO3\Surf\Domain\Service\ShellCommandService;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;

final class ConsoleKernel
{
    private bool $booted = false;
    private ?Container $container = null;
    private string $environment;
    private ?string $projectDir = null;

    public function __construct(string $environment = 'dev')
    {
        $this->environment = $environment;
    }

    private function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../../Resources/services.php');
    }

    private function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CommandsToApplicationCompilerPass());
        $container->registerForAutoconfiguration(
            ShellCommandServiceAwareInterface::class
        )->addMethodCall(
            'setShellCommandService',
            [new Reference(ShellCommandService::class)]
        );
        $container->registerForAutoconfiguration(
            LoggerAwareInterface::class
        )->addMethodCall(
            'setLogger',
            [new Reference(LoggerInterface::class)]
        );
    }

    public function boot(): void
    {
        if (true === $this->booted) {
            return;
        }
        $this->initializeContainer();
        $this->booted = true;
    }

    public function getContainer(): Container
    {
        if ($this->container === null) {
            throw new \UnexpectedValueException('Boot the kernel before');
        }

        return $this->container;
    }

    private function initializeContainer(): void
    {
        if ($this->container !== null) {
            return;
        }

        foreach (['cache' => $this->getCacheDir()] as $name => $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Unable to create the "%s" directory (%s).', $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to write in the "%s" directory (%s).', $name, $dir));
            }
        }

        $file = $this->getCacheDir() . '/container.php';

        if (file_exists($file)) {
            require_once $file;
            if (!class_exists(\ProjectServiceContainer::class, false)) {
                throw new \UnexpectedValueException('Class ProjectServiceContainer does not exist');
            }

            /** @var Container $container */
            $container = new \ProjectServiceContainer();
        } else {
            $container = new ContainerBuilder();
            $loader = new PhpFileLoader($container, new FileLocator());

            $this->registerContainerConfiguration($loader);
            $this->build($container);

            $container->compile();

            $dumper = new PhpDumper($container);
            file_put_contents($file, $dumper->dump());
        }

        $this->container = $container;
    }

    protected function getCacheDir(): string
    {
        if (Phar::running() !== '') {
            return sys_get_temp_dir() . '/_surf';
        }

        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    private function getProjectDir(): string
    {
        if (null === $this->projectDir) {
            $r = new \ReflectionObject($this);

            $dir = $r->getFileName();

            if (!$dir || !is_file($dir)) {
                throw new \LogicException(sprintf('Cannot auto-detect project dir for kernel of class "%s".', $r->name));
            }

            $dir = $rootDir = \dirname($dir);
            while (!is_file($dir . '/composer.json')) {
                if ($dir === \dirname($dir)) {
                    return $this->projectDir = $rootDir;
                }
                $dir = \dirname($dir);
            }
            $this->projectDir = $dir;
        }

        return $this->projectDir;
    }
}
