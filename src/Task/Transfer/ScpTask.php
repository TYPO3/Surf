<?php

namespace TYPO3\Surf\Task\Transfer;

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

/**
 * A scp transfer task
 *
 * Copies the application assets from the application workspace to the node using scp.
 */
final class ScpTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $fileName = sprintf('%s.tar.gz', $deployment->getReleaseIdentifier());

        $localPackagePath = $deployment->getWorkspacePath($application);
        $releasePath = Files::concatenatePaths([$node->getReleasesPath(), $deployment->getReleaseIdentifier()]);

        // Create remote transfer path if not exist
        $remoteTransferPath = Files::concatenatePaths([$node->getDeploymentPath(), 'cache', 'transfer']);
        $this->shell->executeOrSimulate(sprintf('mkdir -p %s', $remoteTransferPath), $node, $deployment);

        // Create the scp destination command
        $destinationArgument = $node->isLocalhost()
            ? $remoteTransferPath
            : sprintf(
                '%s@%s:%s',
                $node->getUsername(),
                $node->getHostname(),
                $remoteTransferPath
            );

        // escape whitespaces
        $localPackagePath = preg_replace('/\s+/', '\ ', $localPackagePath);
        $destinationArgument = preg_replace('/\s+/', '\ ', $destinationArgument);

        // Create a localhost node and create tarball on it
        $localhost = new Node('localhost');
        $localhost->onLocalhost();
        // To prevent issues remove all tar.gz in it before
        $this->shell->executeOrSimulate(sprintf('rm -rf %s/*.tar.gz', $localPackagePath), $localhost, $deployment);
        // Create empty tarball to avoid warning that "file changed as we read it"
        $this->shell->executeOrSimulate(sprintf('cd %s/; touch %s', $localPackagePath, $fileName), $localhost, $deployment);
        // Create tarball locally in the workspace directory
        $this->shell->executeOrSimulate(
            sprintf(
                'cd %1$s/; tar %3$s -czf %2$s -C %1$s .',
                $localPackagePath,
                $fileName,
                $this->getExcludes($options, $fileName)
            ),
            $localhost,
            $deployment,
            false,
            false
        );

        // Transfer tarball to target server
        $this->shell->executeOrSimulate(
            sprintf('scp %s/%s %s', $localPackagePath, $fileName, $destinationArgument),
            $localhost,
            $deployment
        );

        // Extract tarball on server in release path
        $this->shell->executeOrSimulate(
            sprintf('tar -xzf %s/%s -C %s', $remoteTransferPath, $fileName, $releasePath),
            $node,
            $deployment
        );

        // Delete tarball on localhost and Server
        $this->shell->executeOrSimulate(sprintf('rm -f %s/%s', $remoteTransferPath, $fileName), $node, $deployment);
        $this->shell->executeOrSimulate(sprintf('rm -f %s/%s', $localPackagePath, $fileName), $localhost, $deployment);
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $releasePath = $deployment->getApplicationReleasePath($node);
        $this->shell->execute(sprintf('rm -rf %s', $releasePath), $node, $deployment, true);
    }

    /**
     * @return string
     */
    private function getExcludes(array $options, $fileName)
    {
        $excludes = ['.git', $fileName];
        if (isset($options['scpExcludes']) && is_array($options['scpExcludes'])) {
            $excludes = array_merge($excludes, array_filter($options['scpExcludes']));
        }

        foreach ($excludes as &$exclude) {
            $exclude = '--exclude="' . $exclude . '"';
        }

        return implode(' ', $excludes);
    }
}
