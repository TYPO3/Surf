<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Abstract task for any remote TYPO3 CMS cli action
 */
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

    protected function executeCliCommand(array $cliArguments, Node $node, CMS $application, Deployment $deployment, array $options = [])
    {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $phpBinaryPathAndFilename = isset($options['phpBinaryPathAndFilename']) ? $options['phpBinaryPathAndFilename'] : 'php';
        $commandPrefix = '';
        if (isset($options['context'])) {
            $commandPrefix = 'TYPO3_CONTEXT=' . escapeshellarg($options['context']) . ' ';
        }
        $commandPrefix .= $phpBinaryPathAndFilename . ' ';

        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);

        return $this->shell->executeOrSimulate([
            'cd ' . escapeshellarg($this->workingDirectory),
            $commandPrefix . implode(' ', array_map('escapeshellarg', $cliArguments))
        ], $this->targetNode, $deployment);
    }

    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    protected function determineWorkingDirectoryAndTargetNode(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
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
     * @return string
     */
    protected function getAvailableCliPackage(Node $node, CMS $application, Deployment $deployment, array $options = [])
    {
        try {
            $this->getConsoleScriptFileName($node, $application, $deployment, $options);
            return 'typo3_console';
        } catch (InvalidConfigurationException $e) {
        }
        return null;
    }

    /**
     * @return string
     */
    protected function getConsoleScriptFileName(Node $node, CMS $application, Deployment $deployment, array $options = [])
    {
        if (isset($options['scriptFileName']) && strpos($options['scriptFileName'], 'typo3cms') !== false && $this->fileExists($options['scriptFileName'], $node, $application, $deployment, $options)) {
            return $options['scriptFileName'];
        }
        throw new InvalidConfigurationException('TYPO3 Console script was not found. Make sure it is available in your project and you set the "scriptFileName" option correctly. Alternatively you can remove this task (' . get_class($this) . ') in your deployment configuration.', 1481489230);
    }

    /**
     * Checks if a package exists in the packages directory
     *
     * @param string $packageKey
     * @param Node $node
     * @param CMS $application
     * @param Deployment $deployment
     * @param array $options
     * @return bool
     */
    protected function packageExists($packageKey, Node $node, CMS $application, Deployment $deployment, array $options = [])
    {
        $webDirectory = isset($options['webDirectory']) ? trim($options['webDirectory'], '\\/') : '';
        return $this->directoryExists($webDirectory . '/typo3conf/ext/' . $packageKey, $node, $application, $deployment, $options);
    }

    /**
     * Checks if a given directory exists.
     *
     * @param string $directory
     * @return bool
     */
    protected function directoryExists($directory, Node $node, CMS $application, Deployment $deployment, array $options = [])
    {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $directory = Files::concatenatePaths([$this->workingDirectory, $directory]);
        return $this->shell->executeOrSimulate('test -d ' . escapeshellarg($directory), $this->targetNode, $deployment, true) !== false;
    }

    /**
     * Checks if a given file exists.
     *
     * @param string $pathAndFileName
     * @return bool
     */
    protected function fileExists($pathAndFileName, Node $node, CMS $application, Deployment $deployment, array $options = [])
    {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $pathAndFileName = $this->workingDirectory . '/' . $pathAndFileName;
        return $this->shell->executeOrSimulate('test -f ' . escapeshellarg($pathAndFileName), $this->targetNode, $deployment, true) !== false;
    }

    protected function ensureApplicationIsTypo3Cms(Application $application)
    {
        if (!$application instanceof CMS) {
            throw new InvalidConfigurationException(
                'Application must be of type TYPO3 CMS when executing this task!',
                1420210955
            );
        }
    }
}
