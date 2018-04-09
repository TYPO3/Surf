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
     * @var array
     */
    private $neosCommands = array(
        'domain:add',
        'domain:list',
        'domain:delete',
        'domain:activate',
        'domain:deactivate',

        'site:import',
        'site:export',
        'site:prune',
        'site:list',

        'user:list',
        'user:show',
        'user:create',
        'user:delete',
        'user:activate',
        'user:deactivate',
        'user:setpassword',
        'user:addrole',
        'user:removerole',

        'workspace:publish',
        'workspace:discard',
        'workspace:create',
        'workspace:delete',
        'workspace:rebase',
        'workspace:publishall',
        'workspace:discardall',
        'workspace:list'
    );

    /**
     * @param string $command
     * @return bool
     */
    protected function isNeosCommand($command)
    {
        return in_array($command, $this->neosCommands);
    }

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = 'Neos')
    {
        parent::__construct($name);
    }

    /**
     *
     *
     * @return string
     */
    public function getCommandPackageKey($command = '')
    {
        if ($this->getVersion() < '4.0') {
            return $this->isNeosCommand($command) ? 'typo3.neos' : 'typo3.flow';
        }
        return $this->isNeosCommand($command) ? 'neos.neos' : 'neos.flow';
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
