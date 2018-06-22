<?php
namespace TYPO3\Surf\Task\Php;

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
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * A task to reset the PHP opcache by executing a prepared script with an HTTP request.
 *
 * It takes the following options:
 *
 * * baseUrl - The path where the script is located.
 * * scriptIdentifier - The name of the script. Default is a random string. See `WebOpcacheResetCreateScriptTask`
 *   for more information.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask', [
 *              'baseUrl' => '/var/www/outerspace',
 *              'scriptIdentifier' => 'eraseAllHumans'
 *          ]
 *      );
 */
class WebOpcacheResetExecuteTask extends Task
{
    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options Supported options: "baseUrl" (required) and "scriptIdentifier" (is passed by the create script task)
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        if (!isset($options['baseUrl'])) {
            throw new InvalidConfigurationException('No "baseUrl" option provided for WebOpcacheResetExecuteTask', 1421932609);
        }
        if (!isset($options['scriptIdentifier'])) {
            throw new InvalidConfigurationException('No "scriptIdentifier" option provided for WebOpcacheResetExecuteTask, make sure to execute "TYPO3\\Surf\\Task\\Php\\WebOpcacheResetCreateScriptTask" before this task or pass one explicitly', 1421932610);
        }

        $streamContext = null;
        if (isset($options['stream_context']) && is_array($options['stream_context'])) {
            $streamContext = stream_context_create($options['stream_context']);
        }

        $scriptIdentifier = $options['scriptIdentifier'];
        $scriptUrl = rtrim($options['baseUrl'], '/') . '/surf-opcache-reset-' . $scriptIdentifier . '.php';

        $result = file_get_contents($scriptUrl, false, $streamContext);
        if ($result !== 'success') {
            if (isset($options['throwErrorOnWebOpCacheResetExecuteTask']) && $options['throwErrorOnWebOpCacheResetExecuteTask']) {
                throw new TaskExecutionException('WebOpcacheResetExecuteTask at "' . $scriptUrl . '" did not return expected result', 1471511860);
            }
            $deployment->getLogger()->warning('Executing PHP opcache reset script at "' . $scriptUrl . '" did not return expected result');
        }
    }
}
