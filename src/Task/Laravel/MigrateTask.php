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

class MigrateTask extends AbstractCliTask
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        Assert::isInstanceOf(
            $application,
            Laravel::class,
            sprintf('Laravel application needed for %s, got "%s"', get_class($this), get_class($application))
        );

        $this->executeCliCommand(
            ['artisan', 'migrate', '--force'],
            $node,
            $application,
            $deployment,
            $options
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }

    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        Assert::isInstanceOf(
            $application,
            Laravel::class,
            sprintf('Laravel application needed for %s, got "%s"', get_class($this), get_class($application))
        );

        $this->executeCliCommand(
            ['artisan', 'migrate:rollback', '--force'],
            $node,
            $application,
            $deployment,
            $options
        );
    }
}
