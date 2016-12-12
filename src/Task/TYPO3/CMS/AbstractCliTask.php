<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Abstract task for any remote TYPO3 CMS cli action
 */
abstract class AbstractCliTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

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
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param CMS $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return bool|mixed
     */
    protected function executeCliCommand(array $cliArguments, Node $node, CMS $application, Deployment $deployment, array $options = array())
    {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $phpBinaryPathAndFilename = isset($options['phpBinaryPathAndFilename']) ? $options['phpBinaryPathAndFilename'] : 'php';
        $commandPrefix = '';
        if (isset($options['context'])) {
            $commandPrefix = 'TYPO3_CONTEXT=' . escapeshellarg($options['context']) . ' ';
        }
        $commandPrefix .= $phpBinaryPathAndFilename . ' ';

        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);

        return $this->shell->executeOrSimulate(array(
            'cd ' . escapeshellarg($this->workingDirectory),
            $commandPrefix . implode(' ', array_map('escapeshellarg', $cliArguments))
        ), $this->targetNode, $deployment);
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
     * Determines the path to the working directory and the target node by given options
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    protected function determineWorkingDirectoryAndTargetNode(Node $node, Application $application, Deployment $deployment, array $options = array())
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
     * @param Node $node
     * @param CMS $application
     * @param Deployment $deployment
     * @param array $options
     * @return string
     */
    protected function getAvailableCliPackage(Node $node, CMS $application, Deployment $deployment, array $options = array())
    {
        try {
            $this->getConsoleScriptFileName($node, $application, $deployment, $options);
            return 'typo3_console';
        } catch (InvalidConfigurationException $e) {
            if ($this->packageExists('coreapi', $node, $application, $deployment, $options)) {
                return 'coreapi';
            }
        }
        return null;
    }

    /**
     * @param Node $node
     * @param CMS $application
     * @param Deployment $deployment
     * @param array $options
     * @return string
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function getConsoleScriptFileName(Node $node, CMS $application, Deployment $deployment, array $options = array())
    {
        if (isset($options['scriptFileName']) && strpos($options['scriptFileName'], 'typo3cms') !== false && $this->fileExists($options['scriptFileName'], $node, $application, $deployment, $options)) {
            return $options['scriptFileName'];
        } else {
            throw new InvalidConfigurationException('TYPO3 Console script was not found. Make sure it is available in your project and you set the "scriptFileName" option correctly. Alternatively you can remove this task (' . get_class($this) . ') in your deployment configuration.', 1481489230);
        }
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
    protected function packageExists($packageKey, Node $node, CMS $application, Deployment $deployment, array $options = array())
    {
        $webDirectory = isset($options['webDirectory']) ? trim($options['webDirectory'], '\\/') : '';
        return $this->directoryExists($webDirectory . '/typo3conf/ext/' . $packageKey, $node, $application, $deployment, $options);
    }

    /**
     * Checks if a given directory exists.
     *
     * @param string $directory
     * @param Node $node
     * @param CMS $application
     * @param Deployment $deployment
     * @param array $options
     * @return bool
     */
    protected function directoryExists($directory, Node $node, CMS $application, Deployment $deployment, array $options = array())
    {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $directory = Files::concatenatePaths(array($this->workingDirectory, $directory));
        return $this->shell->executeOrSimulate('test -d ' . escapeshellarg($directory), $this->targetNode, $deployment, true) === false ? false : true;
    }

    /**
     * Checks if a given file exists.
     *
     * @param string $pathAndFileName
     * @param Node $node
     * @param CMS $application
     * @param Deployment $deployment
     * @param array $options
     * @return bool
     */
    protected function fileExists($pathAndFileName, Node $node, CMS $application, Deployment $deployment, array $options = array())
    {
        $this->determineWorkingDirectoryAndTargetNode($node, $application, $deployment, $options);
        $pathAndFileName = $this->workingDirectory . '/' . $pathAndFileName;
        return $this->shell->executeOrSimulate('test -f ' . escapeshellarg($pathAndFileName), $this->targetNode, $deployment, true) === false ? false : true;
    }

    /**
     * @param Application $application
     * @throws InvalidConfigurationException
     */
    protected function ensureApplicationIsTypo3Cms(Application $application)
    {
        if (!$application instanceof CMS) {
            throw new InvalidConfigurationException('Application must be of type TYPO3 CMS when executing this task!',
                1420210955);
        }
    }
}
