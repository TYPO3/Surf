<?php

declare(strict_types=1);

namespace TYPO3\Surf\Task;

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
use TYPO3\Surf\Exception\StopWorkflowException;

/**
 * A task that will stop execution inside a workflow (for testing purposes).
 *
 * It doesn't take any options.
 *
 * Example:
 */
class StopTask extends Task
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        throw new StopWorkflowException('Workflow stopped explicitly');
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }
}
