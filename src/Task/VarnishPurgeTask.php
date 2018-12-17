<?php

namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * Task for purging in Varnish, should be used for Varnish 2.x
 *
 * It takes the following options:
 *
 * * secretFile (optional) - Path to the secret file, defaults to "/etc/varnish/secret".
 * * purgeUrl (optional) - URL (pattern) to purge, defaults to ".".
 * * varnishadm (optional) - Path to the varnishadm utility, defaults to "/usr/bin/varnishadm".
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\VarnishPurgeTask', [
 *              'secretFile' => '/etc/varnish/secret',
 *              'purgeUrl' => '.',
 *              'varnishadm' => '/usr/bin/varnishadm'
 *          ]
 *      );
 */
class VarnishPurgeTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);
        $this->shell->executeOrSimulate(sprintf('%s -S %s -T 127.0.0.1:6082 url.purge %s', $options['varnishadm'], $options['secretFile'], $options['purgeUrl']), $node, $deployment);
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
        $options = $this->configureOptions($options);
        $this->shell->executeOrSimulate(sprintf('%s -S %s -T 127.0.0.1:6082 status', $options['varnishadm'], $options['secretFile']), $node, $deployment);
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('secretFile', '/etc/varnish/secret');
        $resolver->setDefault('varnishadm', '/usr/bin/varnishadm');
        $resolver->setDefault('purgeUrl', '.');
    }
}
