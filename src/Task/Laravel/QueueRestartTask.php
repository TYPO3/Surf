<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Laravel;

use TYPO3\Surf\Application\Laravel;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use Webmozart\Assert\Assert;

class QueueRestartTask extends AbstractCliTask
{
    /**
     * @param array<string,mixed> $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        Assert::isInstanceOf(
            $application,
            Laravel::class,
            sprintf('Laravel application needed for %s, got "%s"', get_class($this), get_class($application))
        );

        $this->executeCliCommand(
            ['artisan', 'queue:restart'],
            $node,
            $application,
            $deployment,
            $options
        );
    }
}
