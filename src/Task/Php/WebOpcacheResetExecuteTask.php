<?php
namespace TYPO3\Surf\Task\Php;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * @deprecated Not needed anymore. Just use WebOpcacheResetTask directly
 */
class WebOpcacheResetExecuteTask extends WebOpcacheResetTask
{
    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options Supported options: "baseUrl" (required) and "scriptIdentifier" (is passed by the create script task)
     * @return void
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        parent::execute($node, $application, $deployment, $options);
        $deployment->getLogger()->notice('This task is not needed anymore. Just use WebOpcacheResetTask directly');
    }
}
