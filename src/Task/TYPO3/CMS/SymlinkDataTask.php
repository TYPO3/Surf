<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Utility\Files;
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
        $webDirectory = isset($options['webDirectory']) ? trim($options['webDirectory'], '\\/') : '';
        $relativeDataPath = $relativeDataPathFromWeb = '../../shared/Data';
        if ($webDirectory !== '') {
            $relativeDataPathFromWeb = str_repeat('../', substr_count(trim($webDirectory, '/'), '/') + 1) . $relativeDataPath;
        }
        $absoluteWebDirectory = escapeshellarg(rtrim("$targetReleasePath/$webDirectory", '/'));
        $commands = array(
            'cd ' . escapeshellarg($targetReleasePath),
            "{ [ -d {$relativeDataPath}/fileadmin ] || mkdir -p {$relativeDataPath}/fileadmin ; }",
            "{ [ -d {$relativeDataPath}/uploads ] || mkdir -p {$relativeDataPath}/uploads ; }",
            "rm -rf {$absoluteWebDirectory}/fileadmin",
            "rm -rf {$absoluteWebDirectory}/uploads",
            "ln -sf {$relativeDataPathFromWeb}/fileadmin {$absoluteWebDirectory}/fileadmin",
            "ln -sf {$relativeDataPathFromWeb}/uploads {$absoluteWebDirectory}/uploads"
        );
        if (isset($options['directories']) && is_array($options['directories'])) {
            foreach ($options['directories'] as $directory) {
                $directory = trim($directory, '\\/');
                $targetDirectory = Files::concatenatePaths(array($relativeDataPath, $directory));
                $commands[] = '{ [ -d ' . escapeshellarg($targetDirectory) . ' ] || mkdir -p ' . escapeshellarg($targetDirectory) . ' ; }';
                $commands[] = 'ln -sf ' . escapeshellarg(str_repeat('../', substr_count(trim($directory, '/'), '/')) . $targetDirectory) . ' ' . escapeshellarg($directory);
            }
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
