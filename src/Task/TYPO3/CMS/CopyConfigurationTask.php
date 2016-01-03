<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf.CMS".*
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
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $options['username'] = isset($options['username']) ? $options['username'] . '@' : '';
        $targetReleasePath = $deployment->getApplicationReleasePath($application);
        $configurationPath = $deployment->getDeploymentConfigurationPath() . '/';
        if (!is_dir($configurationPath)) {
            return;
        }
        $configurations = glob($configurationPath . '*');
        $commands = array();
        foreach ($configurations as $configuration) {
            $targetConfigurationPath = dirname(str_replace($configurationPath, '', $configuration));
            if ($node->isLocalhost()) {
                $commands[] = "mkdir -p '{$targetReleasePath}/Configuration/{$targetConfigurationPath}/'";
                $commands[] = "cp {$configuration} {$targetReleasePath}/Configuration/{$targetConfigurationPath}/";
            } else {
                $username = $options['username'];
                $hostname = $node->getHostname();
                $port = $node->hasOption('port') ? '-P ' . escapeshellarg($node->getOption('port')) : '';
                $commands[] = "ssh {$port} {$username}{$hostname} 'mkdir -p {$targetReleasePath}/Configuration/{$targetConfigurationPath}/'";
                $commands[] = "scp {$port} {$configuration} {$username}{$hostname}:{$targetReleasePath}/Configuration/{$targetConfigurationPath}/";
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
