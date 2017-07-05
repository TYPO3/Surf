<?php
namespace TYPO3\Surf\Application\Neos;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;

/**
 * A Neos application template
 *
 */
class Neos extends Flow
{
    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = 'Neos')
    {
        parent::__construct($name);
    }

    /**
     * Register tasks for this application
     *
     * @param Workflow $workflow
     * @param Deployment $deployment
     * @return void
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment)
    {
        parent::registerTasks($workflow, $deployment);

        $workflow->addTask('TYPO3\\Surf\\Task\\Neos\\Neos\\ImportSiteTask', 'migrate', $this);
    }
}
