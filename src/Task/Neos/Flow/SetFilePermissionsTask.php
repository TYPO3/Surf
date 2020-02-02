<?php

namespace TYPO3\Surf\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use Webmozart\Assert\Assert;

/**
 * This tasks sets the file permissions for the Neos Flow application
 *
 * It takes the following options:
 *
 * * shellUsername (optional)
 * * webserverUsername (optional)
 * * webserverGroupname (optional)
 * * phpBinaryPathAndFilename (optional) - path to the php binary default php
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\SetFilePermissionsTask::class, [
 *              'shellUsername' => 'root',
 *              'webserverUsername' => 'www-data',
 *              'webserverGroupname' => 'www-data',
 *              'phpBinaryPathAndFilename', '/path/to/php',
 *          ]
 *      );
 */
class SetFilePermissionsTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application|Flow $application
     * @param Deployment $deployment
     * @param array $options
     *
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        Assert::isInstanceOf($application, Flow::class, sprintf('Flow application needed for SetFilePermissionsTask, got "%s"', get_class($application)));

        $targetPath = $deployment->getApplicationReleasePath($application);

        $options = $this->configureOptions($options);

        $arguments = [
            $options['shellUsername'],
            $options['webserverUsername'],
            $options['webserverGroupname'],
        ];

        $this->shell->executeOrSimulate($application->buildCommand(
            $targetPath,
            'core:setfilepermissions',
            $arguments,
            $options['phpBinaryPathAndFilename']
        ), $node, $deployment);
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
        $resolver->setDefault('username', 'root');

        $resolver->setDefault('shellUsername', static function (Options $options) {
            return $options['username'];
        });

        $resolver->setDefault('webserverUsername', 'www-data');
        $resolver->setDefault('webserverGroupname', 'www-data');

        $resolver->setDefault('phpBinaryPathAndFilename', 'php')
            ->setAllowedTypes('phpBinaryPathAndFilename', 'string');
    }
}
