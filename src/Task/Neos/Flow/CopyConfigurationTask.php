<?php
namespace TYPO3\Surf\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * A task for copying local configuration to the application
 */
class CopyConfigurationTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Executes this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @throws TaskExecutionException
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $configurationFileExtension = isset($options['configurationFileExtension']) ? $options['configurationFileExtension'] : 'yaml';
        $targetReleasePath = $deployment->getApplicationReleasePath($application);
        $configurationPath = $deployment->getDeploymentConfigurationPath();
        if (!is_dir($configurationPath)) {
            return;
        }
        $commands = array();
        $configurationFiles = Files::readDirectoryRecursively($configurationPath, $configurationFileExtension);
        foreach ($configurationFiles as $configuration) {
            $targetConfigurationPath = dirname(str_replace($configurationPath, '', $configuration));
            $escapedSourcePath = escapeshellarg($configuration);
            $escapedTargetPath = escapeshellarg(Files::concatenatePaths(array($targetReleasePath, 'Configuration', $targetConfigurationPath)) . '/');
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
                    $sshOptions .= "-o PubkeyAuthentication=no ";
                    $passwordSshLoginScriptPathAndFilename = Files::concatenatePaths(array(dirname(dirname(dirname(dirname(__DIR__)))), 'Resources', 'Private/Scripts/PasswordSshLogin.expect'));
                    if (\Phar::running() !== '') {
                        $passwordSshLoginScriptContents = file_get_contents($passwordSshLoginScriptPathAndFilename);
                        $passwordSshLoginScriptPathAndFilename = Files::concatenatePaths(array($deployment->getTemporaryPath(), 'PasswordSshLogin.expect'));
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
        $localhost->setHostname('localhost');

        $this->shell->executeOrSimulate($commands, $localhost, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
