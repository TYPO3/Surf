<?php
namespace TYPO3\Surf\Task;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A shell task for local packaging
 */
class LocalShellTask extends AbstractShellTask
{

    /**
     * Executes this task
     *
     * Options:
     *   command: The command to execute
     *   rollbackCommand: The command to execute as a rollback (optional)
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $localhost = new Node('localhost');
        $localhost->setHostname('localhost');

        parent::execute($localhost, $application, $deployment, $options);
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
        $localhost = new Node('localhost');
        $localhost->setHostname('localhost');

        parent::rollback($localhost, $application, $deployment, $options);
    }

    /**
     * @param $command
     * @param Application $application
     * @param Deployment $deployment
     *
     * @return mixed
     */
    protected function prepareCommand($command, Application $application, Deployment $deployment)
    {
        $replacePaths = array();
        $replacePaths['{workspacePath}'] = escapeshellarg($deployment->getWorkspacePath($application));
        $command = str_replace(array_keys($replacePaths), $replacePaths, $command);

        return parent::prepareCommand($command, $application, $deployment);
    }


}
