<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Laravel;

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

class CreateDirectoriesTask extends \TYPO3\Surf\Task\Generic\CreateDirectoriesTask
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $options = [
            'directories' => [
                'shared/storage/app/public',
                'shared/storage/framework/cache/data',
                'shared/storage/framework/sessions',
                'shared/storage/framework/testing',
                'shared/storage/framework/views',
            ],
            'baseDirectory' => $application->getDeploymentPath()
        ];
        parent::execute($node, $application, $deployment, $options);
    }
}
