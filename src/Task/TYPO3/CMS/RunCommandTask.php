<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\TYPO3\CMS;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use Webmozart\Assert\Assert;

/**
 * Task for running arbitrary TYPO3 commands
 */
class RunCommandTask extends AbstractCliTask
{
    /**
     * @param array<string,array<string,mixed>|string> $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        Assert::isInstanceOf($application, CMS::class);
        /** @var CMS $application */
        $options = $this->configureOptions($options);

        $arguments = array_merge([$this->getTypo3ConsoleScriptFileName($node, $application, $deployment, $options), $options['command']], $options['arguments']);

        $this->executeCliCommand(
            $arguments,
            $node,
            $application,
            $deployment,
            $options
        );
    }

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('command');
        $resolver->setDefault('arguments', []);
        $resolver->setNormalizer('arguments', fn (Options $options, $value): array => (array)$value);
    }
}
