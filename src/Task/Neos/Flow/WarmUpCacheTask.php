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
use TYPO3\Surf\Application\Neos\Flow as FlowApplication;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use Webmozart\Assert\Assert;

/**
 * This tasks warms up the Flow Framework cache
 */
class WarmUpCacheTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        Assert::isInstanceOf(
            $application,
            FlowApplication::class,
            sprintf('Flow application needed for WarmUpCacheTask, got "%s"', get_class($application))
        );

        $options = $this->configureOptions($options);

        $targetPath = $deployment->getApplicationReleasePath($application);

        $this->shell->executeOrSimulate(
            $application->buildCommand($targetPath, 'cache:warmup', [], $options['phpBinaryPathAndFilename']),
            $node,
            $deployment
        );
    }

    /**
     * @codeCoverageIgnore
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
