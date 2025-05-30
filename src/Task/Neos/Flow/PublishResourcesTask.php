<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Neos\Flow;

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
 * This task publishes static and non static resources utilizing the resource:publish command
 *
 * It takes the following options:
 *
 * * phpBinaryPathAndFilename (optional) - path to the php binary default php
 */
class PublishResourcesTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @param array<string,mixed> $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        Assert::isInstanceOf($application, Flow::class, sprintf('Flow application needed for PublishResourcesTask, got "%s"', get_class($application)));
        $options = $this->configureOptions($options);

        if ($application->getVersion() >= '3.0') {
            $targetPath = $deployment->getApplicationReleasePath($node);
            $this->shell->executeOrSimulate($application->buildCommand($targetPath, 'resource:publish', [], $options['phpBinaryPathAndFilename']), $node, $deployment);
        }
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
        $resolver->setDefault('phpBinaryPathAndFilename', 'php')
            ->setAllowedTypes('phpBinaryPathAndFilename', 'string');
    }
}
