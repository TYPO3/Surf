<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A task to copy host/context specific configuration
 */
class CopyConfigurationTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * Executes this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $sourceConfigurationPath = $deployment->getDeploymentConfigurationPath() . '/';
        $targetConfigurationPath = $deployment->getApplicationReleasePath($application) . '/Configuration';

        if (!is_dir($sourceConfigurationPath)) {
            return;
        }
        $configurationFilePaths = glob($sourceConfigurationPath . '*');
        $commands = array();

        foreach ($configurationFilePaths as $configurationFilePath) {
            if ($node->isLocalhost()) {
                $commands[] = 'cp ' . escapeshellarg($configurationFilePath) . ' ' . escapeshellarg($targetConfigurationPath);
            } else {
                $username = isset($options['username']) ? $options['username'] . '@' : '';
                $hostname = $node->getHostname();

                $scpPort = $node->hasOption('port') ? '-P ' . escapeshellarg($node->getOption('port')) : '';

                // escape whitespaces for scp
                $scpTargetConfigurationFilePath = str_replace(' ', '\ ', $targetConfigurationPath);
                $commands[] = "scp {$scpPort} " . escapeshellarg($configurationFilePath) . " {$username}{$hostname}:" . escapeshellarg($scpTargetConfigurationFilePath);
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
