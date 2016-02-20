<?php
namespace TYPO3\Surf\Task\Transfer;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A rsync transfer task
 *
 * Copies the application assets from the application workspace to the node using rsync.
 */
class RsyncTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $localPackagePath = $deployment->getWorkspacePath($application);
        $releasePath = $deployment->getApplicationReleasePath($application);

        $remotePath = $application->getDeploymentPath() . '/cache/transfer';
        // make sure there is a remote .cache folder
        $command = 'mkdir -p ' . $remotePath;
        $this->shell->executeOrSimulate($command, $node, $deployment);

        $username = $node->hasOption('username') ? $node->getOption('username') . '@' : '';
        $hostname = $node->getHostname();
        $port = $node->hasOption('port') ? ' -p ' . escapeshellarg($node->getOption('port')) : '';
        $key = $node->hasOption('privateKeyFile') ? ' -i ' . escapeshellarg($node->getOption('privateKeyFile')) : '';
        $quietFlag = (isset($options['verbose']) && $options['verbose']) ? '' : '-q';
        $rshFlag = ($node->isLocalhost() ? '' : '--rsh="ssh' . $port . $key . '" ');

        $rsyncExcludes = isset($options['rsyncExcludes']) ? $options['rsyncExcludes'] : ['.git'];
        $excludeFlags = $this->getExcludeFlags($rsyncExcludes);

        $rsyncFlags = isset($options['rsyncFlags']) ? $options['rsyncFlags'] : '--recursive --times --perms --links --delete --delete-excluded' . $excludeFlags;

        $destinationArgument = ($node->isLocalhost() ? $remotePath : "{$username}{$hostname}:{$remotePath}");

        $command = "rsync {$quietFlag} --compress {$rshFlag} {$rsyncFlags} " . escapeshellarg($localPackagePath . '/.') . ' ' . escapeshellarg($destinationArgument);

        if ($node->hasOption('password')) {
            $resourcesPath = realpath(__DIR__ . '/../../../Resources');
            $passwordSshLoginScriptPathAndFilename = $resourcesPath . '/Private/Scripts/PasswordSshLogin.expect';
            $command = sprintf('expect %s %s %s', escapeshellarg($passwordSshLoginScriptPathAndFilename), escapeshellarg($node->getOption('password')), $command);
        }

        $localhost = new Node('localhost');
        $localhost->setHostname('localhost');
        $this->shell->executeOrSimulate($command, $localhost, $deployment);

        $command = strtr("cp -RPp $remotePath/. $releasePath", "\t\n", '  ');
        // TODO Copy revision file (if it exists) for application to deployment path with release identifier

        $this->shell->executeOrSimulate($command, $node, $deployment);
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

    /**
     * Rollback this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $releasePath = $deployment->getApplicationReleasePath($application);
        $this->shell->execute('rm -Rf ' . $releasePath, $node, $deployment, true);
    }

    /**
     * Generates the --exclude flags for a given array of exclude patterns
     *
     * Example: ['foo', '/bar'] => --exclude 'foo' --exclude '/bar'
     *
     * @param array $rsyncExcludes An array of patterns to be excluded
     * @return string
     */
    protected function getExcludeFlags($rsyncExcludes)
    {
        return array_reduce($rsyncExcludes, function($excludeOptions, $pattern) {
            return $excludeOptions . ' --exclude ' . escapeshellarg($pattern);
        }, '');
    }
}
