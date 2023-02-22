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
use TYPO3\Surf\Application\Neos\Flow as FlowApplication;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use Webmozart\Assert\Assert;

/**
 * This tasks clears the list of Flow Framework cache
 *
 * It takes the following options:
 *
 * * flushCacheList (required) - An array with extension keys to install.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\FlushCacheListTask::class, [
 *              'flushCacheList' => [
 *                  'Neos_Fusion_Content',
 *                  'Flow_Session_MetaData',
 *                  'Flow_Session_Storage',
 *              ],
 *              'phpBinaryPathAndFilename', '/path/to/php',
 *          ]
 *      );
 */
class FlushCacheListTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        Assert::isInstanceOf($application, FlowApplication::class, sprintf('Flow application needed for FlushCacheListTask, got "%s"', get_class($application)));
        Assert::greaterThanEq($application->getVersion(), '2.3', sprintf('FlushCacheListTask is available since Flow Framework 2.3, your application version is "%s"', $application->getVersion()));

        $options = $this->configureOptions($options);

        $targetPath = $deployment->getApplicationReleasePath($node);

        foreach ($options['flushCacheList'] as $cache) {
            $this->logger->debug(sprintf('Flush cache with identifier "%s"', $cache));
            $this->shell->executeOrSimulate(
                $application->buildCommand($targetPath, 'cache:flushone', ['--identifier', $cache], $options['phpBinaryPathAndFilename']),
                $node,
                $deployment
            );
        }
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
        $resolver->setRequired('flushCacheList')
            ->setAllowedTypes('flushCacheList', ['array', 'string'])
            ->setNormalizer('flushCacheList', static fn (Options $options, $value) => is_array($value) ? $value : explode(',', $value))
            ->setAllowedValues('flushCacheList', static function ($value): bool {
                if (is_array($value)) {
                    return !empty($value);
                }
                if (is_string($value)) {
                    return trim($value) !== '';
                }
                return false;
            });

        $resolver->setDefault('phpBinaryPathAndFilename', 'php')
            ->setAllowedTypes('phpBinaryPathAndFilename', 'string');
    }
}
