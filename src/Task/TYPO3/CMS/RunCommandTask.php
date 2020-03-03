<?php

namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use Webmozart\Assert\Assert;

/**
 * Task for running arbitrary TYPO3 commands
 */
class RunCommandTask extends AbstractCliTask
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        Assert::isInstanceOf($application, CMS::class);

        $options = $this->configureOptions($options);

        $arguments = array_merge([$this->getConsoleScriptFileName($node, $application, $deployment, $options), $options['command']], $options['arguments']);

        $this->executeCliCommand(
            $arguments,
            $node,
            $application,
            $deployment,
            $options
        );
    }

    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('command');
        $resolver->setDefault('arguments', []);
        $resolver->setNormalizer('arguments', function (Options $options, $value) {
            return (array)$value;
        });
    }
}
