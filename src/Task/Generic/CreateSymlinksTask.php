<?php
namespace TYPO3\Surf\Task\Generic;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

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
class CreateSymlinksTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Executes this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if (!isset($options['symlinks']) || !is_array($options['symlinks'])) {
            return;
        }

        if (isset($options['genericSymlinksBaseDir']) && !empty($options['genericSymlinksBaseDir'])) {
            $baseDirectory = $options['genericSymlinksBaseDir'];
        } else {
            $baseDirectory = $deployment->getApplicationReleasePath($application);
        }

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
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
