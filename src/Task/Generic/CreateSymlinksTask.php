<?php
namespace TYPO3\Surf\Task\Generic;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A task to create symlinks on target node.
 *
 * The symlink option contains an array of symlink definitions. The array index is the link to be created (relative to
 * the current application release path. The value is the path to the existing file/directory (absolute or relative to the link)
 *
 * Example:
 * $options['symlinks'] = array(
 *   'Web/foobar' => '/tmp/foobar',           # absolute link
 *   'Web/foobaz' => '../../../shared/Data/foobaz', # relative link into the shared folder
 * );
 */
class CreateSymlinksTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
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
        if (!isset($options['symlinks']) || !is_array($options['symlinks'])) {
            return;
        }

        $baseDirectory = isset($options['baseDirectory']) ? $options['baseDirectory'] : $deployment->getApplicationReleasePath($application);

        $commands = array(
            'cd ' . $baseDirectory
        );
        foreach ($options['symlinks'] as $linkPath => $sourcePath) {
            $commands[] = 'ln -s ' . $sourcePath . ' ' . $linkPath;
        }
        $this->shell->executeOrSimulate($commands, $node, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
