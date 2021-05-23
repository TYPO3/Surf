<?php
namespace TYPO3\Surf\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A task to create Neos Flow specific directories
 *
 * It takes no options
 */
class CreateDirectoriesTask extends \TYPO3\Surf\Task\Generic\CreateDirectoriesTask
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = [
            'directories' => [
                'shared/Data/Logs',
                'shared/Data/Persistent',
                'shared/Configuration'
            ],
            'baseDirectory' => $node->getDeploymentPath()
        ];
        parent::execute($node, $application, $deployment, $options);
    }
}
