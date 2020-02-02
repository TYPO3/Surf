<?php

namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * A task to synchronize folders from the machine that runs Surf to a remote host by using Rsync.
 *
 * It takes the following options:
 *
 * * folders - An array with folders to synchronize. The key holds the source folder, the value holds the target folder.
 *   The target folder must have an absolute path.
 * * username (optional) - The username to log in on the remote host.
 * * ignoreErrors (optional) - If true, ignore errors during execution. Default is true.
 * * logOutput (optional) - If true, output the log. Default is false.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\RsyncFoldersTask', [
 *              'folders' => [
 *                  ['uploads/spaceship', '/var/www/outerspace/uploads/spaceship'],
 *                  ['uploads/freighter', '/var/www/outerspace/uploads/freighter'],
 *                  ['/tmp/outerspace/lonely_planet', '/var/www/outerspace/uploads/lonely_planet']
 *                  '/tmp/outerspace/lonely_planet' => '/var/www/outerspace/uploads/lonely_planet'
 *              ]
 *          ]
 *      );
 */
class RsyncFoldersTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        if (empty($options['folders'])) {
            return;
        }

        $replacePaths = [
            '{deploymentPath}' => escapeshellarg($application->getDeploymentPath()),
            '{sharedPath}' => escapeshellarg($application->getSharedPath()),
            '{releasePath}' => escapeshellarg($deployment->getApplicationReleasePath($application)),
            '{currentPath}' => escapeshellarg($application->getReleasesPath() . '/current'),
            '{previousPath}' => escapeshellarg($application->getReleasesPath() . '/previous'),
        ];

        // Build commands to transfer folders
        $commands = array_map(static function (array $folderPair) use ($replacePaths, $options, $node) {
            $sourceFolder = rtrim(str_replace(array_keys($replacePaths), $replacePaths, $folderPair[0]), '/') . '/';
            $targetFolder = rtrim(str_replace(array_keys($replacePaths), $replacePaths, $folderPair[1]), '/') . '/';

            $port = $node->hasOption('port') ? ' -P ' . escapeshellarg($node->getOption('port')) : '';

            return sprintf('rsync -avz --delete -e ssh%s %s %s%s:%s', $port, $sourceFolder, $options['username'], $node->getHostname(), $targetFolder);
        }, $options['folders']);

        $localhost = new Node('localhost');
        $localhost->onLocalhost();

        $this->shell->executeOrSimulate($commands, $localhost, $deployment, $options['ignoreErrors'], $options['logOutput']);
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
     * @param OptionsResolver $resolver
     */
    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('ignoreErrors', false);
        $resolver->setDefault('logOutput', true);

        $resolver->setDefault('username', '');
        $resolver->setNormalizer('username', static function (Options $options, $value) {
            if ($value === '') {
                return $value;
            }

            return sprintf('%s@', $value);
        });

        $resolver->setDefault('folders', []);
        $resolver->setAllowedTypes('folders', 'array');
        $resolver->setNormalizer('folders', static function (Options $options, $value) {
            $folders = [];
            foreach ($value as $folderKey => $folderValue) {
                if (is_array($folderValue) && count($folderValue) === 2) {
                    $folders[] = $folderValue;
                } elseif (is_string($folderValue)) {
                    $folders[] = [$folderKey, $folderValue];
                }
            }

            return $folders;
        });
    }
}
