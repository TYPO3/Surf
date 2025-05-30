<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task;

use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * Task for banning in Varnish, should be used for Varnish 3.x.
 *
 * It takes the following options:
 *
 * * secretFile (optional) - Path to the secret file, defaults to "/etc/varnish/secret".
 * * banUrl (optional) - URL (pattern) to ban, defaults to ".*".
 * * varnishadm (optional) - Path to the varnishadm utility, defaults to "/usr/bin/varnishadm".
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\VarnishBanTask', [
 *              'secretFile' => '/etc/varnish/secret',
 *              'banUrl' => '.*',
 *              'varnishadm' => '/usr/bin/varnishadm'
 *          ]
 *      );
 */
class VarnishBanTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @param array<string,string> $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $options = $this->configureOptions($options);
        /** @var array<string, string> $options */
        $this->shell->executeOrSimulate($options['varnishadm'] . ' -S ' . $options['secretFile'] . ' -T 127.0.0.1:6082 ban.url ' . escapeshellarg($options['banUrl']), $node, $deployment);
    }

    /**
     * @param array<string,string> $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $options = $this->configureOptions($options);
        /** @var array<string, string> $options */
        $this->shell->executeOrSimulate($options['varnishadm'] . ' -S ' . $options['secretFile'] . ' -T 127.0.0.1:6082 status', $node, $deployment);
    }

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('secretFile', '/etc/varnish/secret');
        $resolver->setDefault('varnishadm', '/usr/bin/varnishadm');
        $resolver->setDefault('banUrl', '.*');
    }
}
