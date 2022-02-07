<?php

declare(strict_types = 1);

namespace TYPO3\Surf\Cli\Symfony;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;
use TYPO3\Surf\Cli\Symfony\CompilerPasses\CommandsToApplicationCompilerPass;
use TYPO3\Surf\Domain\Service\ShellCommandService;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;

final class ConsoleKernel extends Kernel
{
    /**
     * @inheritDoc
     */
    public function registerBundles(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../../Resources/services.yaml');
    }

    /**
     * @inheritDoc
     */
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CommandsToApplicationCompilerPass());
        $container->registerForAutoconfiguration(
            ContainerAwareInterface::class
        )->addMethodCall(
            'setContainer',
            [new Reference(ContainerInterface::class)]
        );
        $container->registerForAutoconfiguration(
            ShellCommandServiceAwareInterface::class
        )->addMethodCall(
            'setShellCommandService',
            [new Reference(ShellCommandService::class)]
        );
    }

    public function getCacheDir(): string
    {
        // manually configured, so it can be replaced in phar
        return sys_get_temp_dir() . '/_surf';
    }

    public function getLogDir(): string
    {
        // manually configured, so it can be replaced in phar
        return sys_get_temp_dir() . '/_surf_log';
    }
}
