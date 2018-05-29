<?php
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
use TYPO3\Surf\ErrorMessageFactory;

/**
 * This task create new tables or add new fields to them.
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
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
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
    protected function getSuitableCliArguments(Node $node, CMS $application, Deployment $deployment, array $options = array())
    {
        switch ($this->getAvailableCliPackage($node, $application, $deployment, $options)) {
            case 'typo3_console':
                $databaseCompareMode = isset($options['databaseCompareMode']) ? $options['databaseCompareMode'] : '*.add,*.change';
                return array($this->getConsoleScriptFileName($node, $application, $deployment, $options), 'database:updateschema', $databaseCompareMode);
            case 'coreapi':
                $deployment->getLogger()->warning(ErrorMessageFactory::createDeprecationWarningForCoreApiUsage());
                $databaseCompareMode = isset($options['databaseCompareMode']) ? $options['databaseCompareMode'] : '2,4';
                return array($this->getCliDispatchScriptFileName($options), 'extbase', 'databaseapi:databasecompare', $databaseCompareMode);
            default:
                return array();
        }
    }
}
