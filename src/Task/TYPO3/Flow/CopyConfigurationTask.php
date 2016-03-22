<?php
namespace TYPO3\Surf\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A task for copying local configuration to the application
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
        $configurationFileExtension = isset($options['configurationFileExtension']) ? $options['configurationFileExtension'] : 'yaml';
        $targetReleasePath = $deployment->getApplicationReleasePath($application);
        $configurationPath = $deployment->getDeploymentConfigurationPath();
        if (!is_dir($configurationPath)) {
            return;
        }

        $encryptedConfigurationFiles = Finder::create()
            ->files()
            ->name('*.' . $configurationFileExtension . '.encrypted')
            ->in($configurationPath);

        if ($encryptedConfigurationFiles->count() > 0) {
            throw new \TYPO3\Surf\Exception\TaskExecutionException('You have sealed configuration files, please open the configuration for "' . $deployment->getName() . '"', 1317229449);
        }
        $configurationFiles = Finder::create()
            ->files()
            ->name('*.' . $configurationFileExtension)
            ->in($configurationPath);
        $commands = array();
        /** @var SplFileInfo $configuration */
        foreach ($configurationFiles as $configuration) {
            $targetConfigurationPath = $configuration->getRelativePath() ? $configuration->getRelativePath() . '/' : '';
            $escapedSourcePath = escapeshellarg("{$configuration->getRealPath()}");
            $escapedTargetPath = escapeshellarg("{$targetReleasePath}/Configuration/{$targetConfigurationPath}");
            if ($node->isLocalhost()) {
                $commands[] = 'mkdir -p ' . $escapedTargetPath;
                $commands[] = 'cp ' . $escapedSourcePath . ' ' . $escapedTargetPath;
            } else {
                $username = isset($options['username']) ? $options['username'] . '@' : '';
                $hostname = $node->getHostname();

                $sshPort = isset($options['port']) ? '-p ' . escapeshellarg($options['port']) . ' ' : '';
                $scpPort = isset($options['port']) ? '-P ' . escapeshellarg($options['port']) . ' ' : '';
                $createDirectoryCommand = '"mkdir -p ' . $escapedTargetPath . '"';
                $commands[] = "ssh {$sshPort}{$username}{$hostname} {$createDirectoryCommand}";
                $commands[] = "scp {$scpPort}{$escapedSourcePath} {$username}{$hostname}:\"{$escapedTargetPath}\"";
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
