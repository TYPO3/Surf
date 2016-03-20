<?php
namespace TYPO3\Surf\Application\TYPO3;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;

/**
 * A TYPO3 Neos application template
 *
 */
class Neos extends \TYPO3\Surf\Application\TYPO3\Flow
{
    /**
     * Constructor
     */
    public function __construct($name = 'TYPO3_Neos')
    {
        parent::__construct($name);
    }

    /**
     * Register tasks for this application
     *
     * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @return void
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment)
    {
        parent::registerTasks($workflow, $deployment);

        $workflow->addTask('TYPO3\\Surf\\Task\\TYPO3\\Neos\\ImportSitesTask', 'migrate', $this);
    }
}
