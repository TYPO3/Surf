<?php

declare(strict_types=1);

namespace TYPO3\Surf\Cli\Symfony\DependencyInjection;

use BadMethodCallException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

abstract class Extension implements ExtensionInterface
{
    public function getXsdValidationBasePath()
    {
        return false;
    }

    public function getNamespace(): string
    {
        return 'http://example.org/schema/dic/' . $this->getAlias();
    }

    public function getAlias(): string
    {
        $className = static::class;
        if (!str_ends_with($className, 'Extension')) {
            throw new BadMethodCallException('This extension does not follow the naming convention; you must overwrite the getAlias() method.');
        }
        $classBaseName = substr(str_replace('\\', '', $className), 0, -9);

        return Container::underscore($classBaseName);
    }

    public function loadExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();
        $reflection = new \ReflectionClass($this);
        $namespace = $reflection->getNamespaceName();
        $services->defaults()
            ->autowire()
            ->autoconfigure()
            ->public();

        $services->load($namespace . '\\', '*');
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $reflection = new \ReflectionClass($this);
        $file = $reflection->getFileName();
        $dirname = dirname($file);
        $fileName = basename($file);

        $env = $container->getParameter('kernel.environment');

        $extensionLoader = new PhpFileLoader($container, new FileLocator());
        $extensionLoader->setCurrentDir($dirname);

        $instanceofClosure = &\Closure::bind(fn &() => $this->instanceof, $extensionLoader, $extensionLoader)();

        (fn(ContainerConfigurator $configurator) => $this->loadExtension($configurator, $container))((new ContainerConfigurator(
            $container,
            $extensionLoader,
            $instanceofClosure,
            $dirname,
            $fileName,
            $env
        )));
    }
}
