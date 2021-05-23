<?php

namespace TYPO3\Surf\Task\Composer;

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
 * Downloads Composer into the current releasePath.
 *
 * It takes the following options:
 *
 * * composerDownloadCommand (optional) - The command that should be used to download Composer instead of the regular command.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Composer\DownloadTask', [
 *              'composerDownloadCommand' => 'curl -s https://getcomposer.org/installer | php'
 *          ]
 *      );
 */
class DownloadTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        $command = sprintf('cd %s && %s', escapeshellarg($deployment->getApplicationReleasePath($node)), $options['composerDownloadCommand']);

        $this->shell->executeOrSimulate($command, $node, $deployment);
    }

    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('composerDownloadCommand', 'curl -s https://getcomposer.org/installer | php');
    }
}
