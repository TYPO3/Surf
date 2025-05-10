<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Neos\Flow;

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
    /**
     * @param array<string,mixed> $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
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
