<?php

namespace TYPO3\Surf\Task\Generic;

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
 * Creates symlinks on target node.
 *
 * It takes the following options:
 *
 * * symlinks - An array of symlinks to create. The array index is the link to be created (relative to the current application
 *   release path). The value is the path to the existing file/directory (absolute or relative to the link).
 *
 * Example:
 *  $options['symlinks'] = array(
 *      'Web/foobar' => '/tmp/foobar', # An absolute link
 *      'Web/foobaz' => '../../../shared/Data/foobaz', # A relative link into the shared folder
 *  );
 */
class CreateSymlinksTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        if (empty($options['symlinks'])) {
            return;
        }

        $baseDirectory = $options['genericSymlinksBaseDir'] ?: $deployment->getApplicationReleasePath($node);

        $commands = [
            'cd ' . $baseDirectory,
        ];

        foreach ($options['symlinks'] as $linkPath => $sourcePath) {
            $commands[] = sprintf('ln -s %s %s', $sourcePath, $linkPath);
        }

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
        $resolver->setDefault('symlinks', []);
        $resolver->setAllowedTypes('symlinks', 'array');
        $resolver->setDefault('genericSymlinksBaseDir', null);
        $resolver->setAllowedTypes('genericSymlinksBaseDir', ['string', 'null']);
        $resolver->setNormalizer('genericSymlinksBaseDir', function (Options $options, $value) {
            return ! empty($value) ? $value : null;
        });
    }
}
