<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Cli\Symfony\CompilerPasses;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\Surf\Cli\Symfony\ConsoleApplication;

final class CommandsToApplicationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $applicationDefinition = $container->getDefinition(ConsoleApplication::class);

        foreach ($container->getDefinitions() as $name => $definition) {
            if (!is_string($definition->getClass())) {
                continue;
            }

            if (!is_a($definition->getClass(), Command::class, true)) {
                continue;
            }

            $applicationDefinition->addMethodCall('add', [new Reference($name)]);
        }
    }
}
