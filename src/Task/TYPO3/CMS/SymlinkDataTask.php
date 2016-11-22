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
 * A symlink task for linking the shared data directory
 * If the symlink target has folder, the folders themselves must exist!
 */
class SymlinkDataTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * Executes this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $targetReleasePath = $deployment->getApplicationReleasePath($application);
        $applicationRootDirectory = isset($options['applicationRootDirectory']) ? trim($options['applicationRootDirectory'], '\\/') : '';
        $workingDirectory = escapeshellarg(rtrim("$targetReleasePath/$applicationRootDirectory", '/'));
        $relativeDataPath = '../../shared/Data';
        if (!empty($applicationRootDirectory)) {
            $relativeDataPath = str_repeat('../', substr_count(trim($applicationRootDirectory, '/'), '/') + 1) . $relativeDataPath;
        }
        $commands = array(
            "cd $workingDirectory",
        );

        if (isset($options['directories']) && is_array($options['directories'])) {
            $directories = $options['directories'];
        } else {
            $directories = array();
        }

        if (!in_array('fileadmin', $directories) && !in_array('./fileadmin', $directories)) {
            $symlinkFileadmin = TRUE;
            foreach ($directories as $directory) {
                if (substr($directory, 0, 10) === 'fileadmin/') {
                    $symlinkFileadmin = FALSE;
                    break;
                }
            }
            if ($symlinkFileadmin) {
                $directories[] = 'fileadmin';
            }
        }
        if (!in_array('uploads', $directories) && !in_array('./uploads', $directories)) {
            $directories[] = 'uploads';
        }

        foreach ($directories as $directory) {
            $targetDirectory = escapeshellarg("{$relativeDataPath}/{$directory}");
            $commands[] = '{ [ -d ' . $targetDirectory . ' ] || mkdir -p ' . $targetDirectory . ' ; }';
            $commands[] = 'ln -sf ' . escapeshellarg(str_repeat('../', substr_count(trim($directory, '/'), '/')) . "$relativeDataPath/$directory") . ' ' . escapeshellarg($directory);
        }
        $this->shell->executeOrSimulate($commands, $node, $deployment);
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
