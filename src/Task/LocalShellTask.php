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
 * A shell task for local packaging.
 *
 * It takes the following options:
 *
 * * command - The command to execute.
 * * rollbackCommand (optional) - The command to execute as a rollback.
 * * ignoreErrors (optional) - If true, ignore errors during execution. Default is true.
 * * logOutput (optional) - If true, output the log. Default is false.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\LocalShellTask', [
 *              'command' => mkdir -p /var/wwww/outerspace',
 *              'rollbackCommand' => 'rm -rf /Var/www/outerspace'
 *          ]
 *      );
 */
class LocalShellTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);
        $replacePaths = [];
        $replacePaths['{workspacePath}'] = escapeshellarg($deployment->getWorkspacePath($application));

        $command = str_replace(array_keys($replacePaths), $replacePaths, $options['command']);

        $localhost = new Node('localhost');
        $localhost->onLocalhost();

        $this->shell->executeOrSimulate($command, $localhost, $deployment, $options['ignoreErrors'], $options['logOutput']);
    }

    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $replacePaths = [];
        $replacePaths['{workspacePath}'] = escapeshellarg($deployment->getWorkspacePath($application));

        if (null === $options['rollbackCommand']) {
            return;
        }

        $command = str_replace(array_keys($replacePaths), $replacePaths, $options['rollbackCommand']);

        $localhost = new Node('localhost');
        $localhost->onLocalhost();

        $this->shell->execute($command, $localhost, $deployment, true);
    }

    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['command']);
        $resolver->setDefault('rollbackCommand', null);
        $resolver->setDefault('ignoreErrors', false);
        $resolver->setDefault('logOutput', false);
    }
}
