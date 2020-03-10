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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use TYPO3\Surf\Cli\Symfony\CompilerPasses\CommandsToApplicationCompilerPass;
use TYPO3\Surf\Cli\Symfony\CompilerPasses\ContainerAwareInterfaceCompilerPass;
use TYPO3\Surf\Cli\Symfony\CompilerPasses\ShellCommandServiceAwareInterfaceCompilerPass;

final class ConsoleKernel extends Kernel
{
    /**
     * @inheritDoc
     */
    public function registerBundles()
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
    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new CommandsToApplicationCompilerPass());
        $containerBuilder->addCompilerPass(new ContainerAwareInterfaceCompilerPass());
        $containerBuilder->addCompilerPass(new ShellCommandServiceAwareInterfaceCompilerPass());
    }
}
