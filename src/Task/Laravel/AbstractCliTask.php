<?php
declare(strict_types=1);

namespace TYPO3\Surf\Task\Laravel;

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

abstract class AbstractCliTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * The working directory. Either local or remote, and probably in a special application root directory
     *
     * @var string
     */
    protected $workingDirectory;

    /**
     * Localhost or deployment target node
     *
     * @var Node
     */
    protected $targetNode;

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

        return $this->shell->executeOrSimulate(
            [
                'cd ' . escapeshellarg($this->workingDirectory),
                $commandPrefix . implode(' ', array_map('escapeshellarg', $cliArguments))
            ],
            $this->targetNode,
            $deployment
        );
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
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
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
    ) {
        if (!isset($this->workingDirectory, $this->targetNode)) {
            if (isset($options['useApplicationWorkspace']) && $options['useApplicationWorkspace'] === true) {
                $this->workingDirectory = $deployment->getWorkspacePath($application);
                $node = $deployment->getNode('localhost');
            } else {
                $this->workingDirectory = $deployment->getApplicationReleasePath($application);
            }
            $this->targetNode = $node;
        }
    }

    /**
     * Checks if a given directory exists.
     *
     * @param string $directory
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return bool
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    protected function directoryExists(
        $directory,
        Node $node,
        Application $application,
        Deployment $deployment,
        array $options = []
    ) {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $directory = Files::concatenatePaths([$this->workingDirectory, $directory]);
        return $this->shell->executeOrSimulate(
                'test -d ' . escapeshellarg($directory),
                $this->targetNode,
                $deployment,
                true
            ) !== false;
    }

    /**
     * Checks if a given file exists.
     *
     * @param string $pathAndFileName
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return bool
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    protected function fileExists(
        $pathAndFileName,
        Node $node,
        Application $application,
        Deployment $deployment,
        array $options = []
    ) {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $pathAndFileName = $this->workingDirectory . '/' . $pathAndFileName;
        return $this->shell->executeOrSimulate(
                'test -f ' . escapeshellarg($pathAndFileName),
                $this->targetNode,
                $deployment,
                true
            ) !== false;
    }
}
