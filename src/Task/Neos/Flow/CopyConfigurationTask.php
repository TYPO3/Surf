<?php
namespace TYPO3\Surf\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Phar;
use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * A task for copying local configuration to the application
 *
 * It takes the following options:
 *
 * * configurationFileExtension (optional) - yaml or something different
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions(\TYPO3\Surf\Task\Neos\Flow\CopyConfigurationTask::class, [
 *              'configurationFileExtension' => 'yaml'
 *          ]
 *      );
 */
class CopyConfigurationTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $configurationFileExtension = isset($options['configurationFileExtension']) ? $options['configurationFileExtension'] : 'yaml';
        $targetReleasePath = $deployment->getApplicationReleasePath($node);
        $configurationPath = $deployment->getDeploymentConfigurationPath();
        if (!is_dir($configurationPath)) {
            return;
        }
        $commands = [];
        $configurationFiles = Files::readDirectoryRecursively($configurationPath, $configurationFileExtension);
        foreach ($configurationFiles as $configuration) {
            $targetConfigurationPath = dirname(str_replace($configurationPath, '', $configuration));
            $escapedSourcePath = escapeshellarg($configuration);
            $escapedTargetPath = escapeshellarg(Files::concatenatePaths([$targetReleasePath, 'Configuration', $targetConfigurationPath]) . '/');
            if ($node->isLocalhost()) {
                $commands[] = 'mkdir -p ' . $escapedTargetPath;
                $commands[] = 'cp ' . $escapedSourcePath . ' ' . $escapedTargetPath;
            } else {
                $username = isset($options['username']) ? $options['username'] . '@' : '';
                $hostname = $node->getHostname();

                $sshPort = isset($options['port']) ? '-p ' . escapeshellarg($options['port']) . ' ' : '';
                $scpPort = isset($options['port']) ? '-P ' . escapeshellarg($options['port']) . ' ' : '';
                $sshOptions = '';
                $expect = '';
                if ($node->hasOption('password')) {
                    $sshOptions .= '-o PubkeyAuthentication=no ';
                    $passwordSshLoginScriptPathAndFilename = Files::concatenatePaths([dirname(dirname(dirname(dirname(__DIR__)))), 'Resources', 'Private/Scripts/PasswordSshLogin.expect']);
                    if (Phar::running() !== '') {
                        $passwordSshLoginScriptContents = file_get_contents($passwordSshLoginScriptPathAndFilename);
                        $passwordSshLoginScriptPathAndFilename = Files::concatenatePaths([$deployment->getTemporaryPath(), 'PasswordSshLogin.expect']);
                        file_put_contents($passwordSshLoginScriptPathAndFilename, $passwordSshLoginScriptContents);
                    }
                    $expect = sprintf('expect %s %s', escapeshellarg($passwordSshLoginScriptPathAndFilename), escapeshellarg($node->getOption('password')));
                }
                $createDirectoryCommand = '"mkdir -p ' . $escapedTargetPath . '"';
                $commands[] = ltrim("{$expect} ssh {$sshOptions}{$sshPort}{$username}{$hostname} {$createDirectoryCommand}");
                $commands[] = ltrim("{$expect} scp {$sshOptions}{$scpPort}{$escapedSourcePath} {$username}{$hostname}:\"{$escapedTargetPath}\"");
            }
        }

        $localhost = new Node('localhost');
        $localhost->onLocalhost();

        $this->shell->executeOrSimulate($commands, $localhost, $deployment);
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
