<?php
declare(strict_types = 1);

namespace TYPO3\Surf\Cli\Symfony\CompilerPasses;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\Surf\Domain\Service\ShellCommandService;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;

final class ShellCommandServiceAwareInterfaceCompilerPass implements CompilerPassInterface
{

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $name => $definition) {
            if (is_a($definition->getClass(), ShellCommandServiceAwareInterface::class, true)) {
                $definition->addMethodCall('setShellCommandService', [new Reference(ShellCommandService::class)]);
            }
        }
    }
}
