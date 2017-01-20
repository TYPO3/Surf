<?php
namespace TYPO3\Surf\Task\Neos\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A task to create Neos Flow specific directories
 *
 */
class CreateDirectoriesTask extends \TYPO3\Surf\Task\Generic\CreateDirectoriesTask
{
    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $options = array(
            'directories' => array(
                'shared/Data/Logs',
                'shared/Data/Persistent',
                'shared/Configuration'
            ),
            'baseDirectory' => $application->getDeploymentPath()
        );
        parent::execute($node, $application, $deployment, $options);
    }
}
