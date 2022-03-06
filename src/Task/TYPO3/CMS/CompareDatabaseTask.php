<?php

declare(strict_types=1);

namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use Webmozart\Assert\Assert;

/**
 * This task create new tables or add new fields to them.
 * This task requires the extension `typo3_console`.
 *
 * It takes the following options:
 *
 * * databaseCompareMode (optional) - The mode in which the database should be compared.
 *   For `typo3_console`, `*.add,*.change` is the default value.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Composer\CompareDatabaseTask'
 *          'databaseCompareMode' => '2'
 *      );
 */
class CompareDatabaseTask extends AbstractCliTask
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        /** @var CMS $application */
        Assert::isInstanceOf($application, CMS::class);
        $cliArguments = $this->getSuitableCliArguments($node, $application, $deployment, $options);
        if (empty($cliArguments)) {
            $deployment->getLogger()->warning('Extension "typo3_console" was not found! Make sure one is available in your project, or remove this task (' . self::class . ') in your deployment configuration!');
            return;
        }
        $this->executeCliCommand(
            $cliArguments,
            $node,
            $application,
            $deployment,
            $options
        );
    }

    protected function getSuitableCliArguments(Node $node, CMS $application, Deployment $deployment, array $options = []): array
    {
        if ($this->getAvailableCliPackage($node, $application, $deployment, $options) === 'typo3_console') {
            $databaseCompareMode = $options['databaseCompareMode'] ?? '*.add,*.change';
            return [$this->getTypo3ConsoleScriptFileName($node, $application, $deployment, $options), 'database:updateschema', $databaseCompareMode];
        }

        return [];
    }
}
