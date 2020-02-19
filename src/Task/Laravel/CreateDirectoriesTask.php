<?php

namespace TYPO3\Surf\Task\Laravel;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A task to create Laravel specific shared directories
 *
 * It takes no options
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
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = [
            'directories' => [
                'shared/Data/storage/app/public',
                'shared/Data/storage/framework/cache/data',
                'shared/Data/storage/framework/sessions',
                'shared/Data/storage/framework/testing',
                'shared/Data/storage/framework/views',
            ],
            'baseDirectory' => $application->getDeploymentPath()
        ];
        parent::execute($node, $application, $deployment, $options);
    }
}
