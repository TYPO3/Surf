<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Neos\Flow;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use Webmozart\Assert\Assert;

/**
 * This task runs Neos Flow commands
 *
 * It takes the following options:
 *
 * * command (required)
 * * arguments
 * * ignoreErrors (optional)
 * * logOutput (optional)
 * * phpBinaryPathAndFilename (optional) - path to the php binary default php
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\RunCommandTask::class, [
 *              'command' => 'flow:help',
 *              'arguments => [],
 *              'ignoreErrors' => false,
 *              'logOutput' => true,
 *              'phpBinaryPathAndFilename', '/path/to/php',
 *          ]
 *      );
 */
class RunCommandTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @param array<string,mixed> $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        Assert::isInstanceOf($application, Flow::class, sprintf('Flow application needed for RunCommandTask, got "%s"', get_class($application)));

        $options = $this->configureOptions($options);

        $targetPath = $deployment->getApplicationReleasePath($node);

        $command = $application->buildCommand($targetPath, $options['command'], $options['arguments'], $options['phpBinaryPathAndFilename']);

        $this->shell->executeOrSimulate($command, $node, $deployment, $options['ignoreErrors'], $options['logOutput']);
    }

    /**
     * @codeCoverageIgnore
     * @param array<string,mixed> $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('ignoreErrors', false);
        $resolver->setDefault('logOutput', true);
        $resolver->setDefault('phpBinaryPathAndFilename', 'php');

        $resolver->setDefault('arguments', []);
        $resolver->setAllowedTypes('arguments', ['array', 'string']);
        $resolver->setNormalizer('arguments', fn (Options $options, $value): array => (array)$value);
        $resolver->setRequired('command')->setAllowedTypes('command', 'string');
    }
}
