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
        $loader->load(__DIR__ . '/../../../Resources/services.php');
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
        $container->registerForAutoconfiguration(
            LoggerAwareInterface::class
        )->addMethodCall(
            'setLogger',
            [new Reference(LoggerInterface::class)]
        );
    }

    public function getCacheDir(): string
    {
        if(Phar::running() !== '') {
            return sys_get_temp_dir() . '/_surf';
        }

        return parent::getCacheDir();
    }

    public function getLogDir(): string
    {
        if(Phar::running() !== '') {
            return sys_get_temp_dir() . '/_surf_log';
        }

        return parent::getLogDir();
    }
}
