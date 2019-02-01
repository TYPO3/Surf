<?php
namespace TYPO3\Surf\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\Neos\Flow as FlowApplication;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;

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
 *                  'Flow_Session_Storage'
 *              ]
 *          ]
 *      );
 */
class FlushCacheListTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!$application instanceof FlowApplication) {
            throw new InvalidConfigurationException(sprintf(
                'Flow application needed for MigrateTask, got "%s"',
                get_class($application)
            ), 1429774224);
        }

        if (!isset($options['flushCacheList']) || trim($options['flushCacheList']) === '') {
            throw new InvalidConfigurationException(
                'Missing option "flushCacheList" for FlushCacheListTask',
                1429774229
            );
        }

        if ($application->getVersion() >= '2.3') {
            $caches = is_array($options['flushCacheList']) ? $options['flushCacheList'] : explode(
                ',',
                $options['flushCacheList']
            );
            $targetPath = $deployment->getApplicationReleasePath($application);
            foreach ($caches as $cache) {
                $deployment->getLogger()->debug(sprintf('Flush cache with identifier "%s"', $cache));
                $command = sprintf('flow:cache:flushone --identifier %s', $cache);
                $this->shell->executeOrSimulate(
                    $application->buildCommand(
                        $targetPath,
                        $application->getApplicationCommand($command),
                        $node,
                        $deployment
                    )
                );
            }
        } else {
            throw new InvalidConfigurationException(sprintf(
                'FlushCacheListTask is available since Flow Framework 2.3, your application version is "%s"',
                $application->getVersion()
            ), 1434126060);
        }
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Rollback the task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        // Unable to rollback a clear cache command
    }
}
