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
 * Clear TYPO3 caches
 * This task requires the extension typo3_console.
 */
class FlushCachesTask extends AbstractCliTask
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        /** @var CMS $application */
        Assert::isInstanceOf($application, CMS::class);

        $options = $this->configureOptions($options);

        $cliArguments = $this->getSuitableCliArguments($node, $application, $deployment, $options);
        if (empty($cliArguments)) {
            $deployment->getLogger()->warning('Extension "typo3_console" was not found! Make sure it is available in your project, or remove this task (' . self::class . ') in your deployment configuration!');
            return;
        }
        $this->executeCliCommand(
            $cliArguments,
            $node,
            $application,
            $deployment,
            $options
        );
    }

    protected function getSuitableCliArguments(Node $node, CMS $application, Deployment $deployment, array $options = []): ?array
    {
        switch ($this->getAvailableCliPackage($node, $application, $deployment, $options)) {
            case 'typo3_console':
                return array_merge([$this->getTypo3ConsoleScriptFileName($node, $application, $deployment, $options), 'cache:flush'], $options['arguments']);
            default:
                return [];
        }
    }

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('arguments', [])
            ->setAllowedTypes('arguments', ['array', 'string'])
            ->setNormalizer('arguments', fn (Options $options, $value): array => (array)$value);
    }
}
