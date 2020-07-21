<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\DeprecationMessageFactory;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * This task create new tables or add new fields to them.
 * This task requires the extensions `coreapi` or `typo3_console`.
 *
 * It takes the following options:
 *
 * * databaseCompareMode (optional) - The mode in which the database should be compared. For `coreapi`, `2,4` is the
 *  default value. For `typo3_console`, `*.add,*.change` is the default value.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Composer\CompareDatabaseTask'
 *          'databaseCompareMode' => '2'
 *      );
 */
class CompareDatabaseTask extends AbstractCliTask
{
    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->ensureApplicationIsTypo3Cms($application);
        $cliArguments = $this->getSuitableCliArguments($node, $application, $deployment, $options);
        if (empty($cliArguments)) {
            $deployment->getLogger()->warning('Neither Extension "typo3_console" nor "coreapi" was not found! Make sure one is available in your project, or remove this task (' . __CLASS__ . ') in your deployment configuration!');
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

    /**
     * @param Node $node
     * @param CMS $application
     * @param Deployment $deployment
     * @param array $options
     * @return array
     */
    protected function getSuitableCliArguments(Node $node, CMS $application, Deployment $deployment, array $options = [])
    {
        switch ($this->getAvailableCliPackage($node, $application, $deployment, $options)) {
            case 'typo3_console':
                $databaseCompareMode = $options['databaseCompareMode'] ?? '*.add,*.change';
                return [$this->getConsoleScriptFileName($node, $application, $deployment, $options), 'database:updateschema', $databaseCompareMode];
            case 'coreapi':
                $deployment->getLogger()->warning(DeprecationMessageFactory::createDeprecationWarningForCoreApiUsage());
                $databaseCompareMode = $options['databaseCompareMode'] ?? '2,4';
                return [$this->getCliDispatchScriptFileName($options), 'extbase', 'databaseapi:databasecompare', $databaseCompareMode];
            default:
                return [];
        }
    }
}
