<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Laravel;

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

abstract class AbstractCliTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * The working directory. Either local or remote, and probably in a special application root directory
     */
    protected ?string $workingDirectory = null;

    /**
     * Localhost or deployment target node
     */
    protected ?Node $targetNode = null;

    /**
     * Execute this task
     *
     * @param array $cliArguments
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return bool|mixed
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    protected function executeCliCommand(
        array $cliArguments,
        Node $node,
        Application $application,
        Deployment $deployment,
        array $options = []
    ) {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $phpBinaryPathAndFilename = $options['phpBinaryPathAndFilename'] ?? 'php';
        $commandPrefix = $phpBinaryPathAndFilename . ' ';

        if (!$this->targetNode instanceof Node) {
            return false;
        }

        return $this->shell->executeOrSimulate([
            'cd ' . escapeshellarg($this->workingDirectory ?? ''),
            $commandPrefix . implode(' ', array_map('escapeshellarg', $cliArguments))
        ], $this->targetNode, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Determines the path to the working directory and the target node by given options
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    protected function determineWorkingDirectoryAndTargetNode(
        Node $node,
        Application $application,
        Deployment $deployment,
        array $options = []
    ): void {
        if (!isset($this->workingDirectory, $this->targetNode)) {
            if (isset($options['useApplicationWorkspace']) && $options['useApplicationWorkspace'] === true) {
                $this->workingDirectory = $deployment->getWorkspacePath($application);
                $node = $deployment->createLocalhostNode();
            } else {
                $this->workingDirectory = $deployment->getApplicationReleasePath($node);
            }
            $this->targetNode = $node;
        }
    }
}
