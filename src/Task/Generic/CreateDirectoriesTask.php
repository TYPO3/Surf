<?php

namespace TYPO3\Surf\Task\Generic;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * Creates directories for a release.
 *
 * It takes the following options:
 *
 * * baseDirectory (optional) - Can be set as base path.
 * * directories - An array of directories to create. The paths can be relative to the baseDirectory, if set.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Generic\CreateDirectoriesTask', [
 *              'baseDirectory' => '/var/www/outerspace',
 *              'directories' => [
 *                  'uploads/spaceship',
 *                  'uploads/freighter',
 *                  '/tmp/outerspace/lonely_planet'
 *              ]
 *          ]
 *      );
 */
class CreateDirectoriesTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        try {
            $options = $this->configureOptions($options);
        } catch (ExceptionInterface $e) {
            return;
        }

        if (empty($options['directories'])) {
            return;
        }

        $baseDirectory = $options['baseDirectory'] ?: $deployment->getApplicationReleasePath($node);

        $commands = array_map(function ($directory) {
            return sprintf('mkdir -p %s', $directory);
        }, $options['directories']);

        array_unshift($commands, sprintf('cd %s', $baseDirectory));

        $this->shell->executeOrSimulate($commands, $node, $deployment);
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('directories');
        $resolver->setDefault('baseDirectory', null);
        $resolver->setAllowedTypes('directories', 'array');
    }
}
