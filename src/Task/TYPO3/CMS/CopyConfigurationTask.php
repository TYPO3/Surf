<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\DeprecationMessageFactory;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A task to copy host/context specific configuration.
 * @deprecated
 *
 * It takes the following options:
 *
 * * configurationFileExtension (optional) - Sets the file extension of the configuration file. Default is `php`.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\TYPO3\CMS\CopyConfigurationTask', [
 *              'configurationFileExtension' => 'json'
 *          ]
 *      );
 */
class CopyConfigurationTask extends \TYPO3\Surf\Task\Neos\Flow\CopyConfigurationTask
{
    /**
     * Executes this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $deployment->getLogger()->warning(DeprecationMessageFactory::createGenericDeprecationWarningForTask(__CLASS__));
        $options['configurationFileExtension'] = $options['configurationFileExtension'] ?? 'php';
        parent::execute($node, $application, $deployment, $options);
    }
}
