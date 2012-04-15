<?php
namespace TYPO3\Surf\Application;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A base application with Git checkout and basic release directory structure
 *
 * Most specific applications will extend from BaseApplication.
 */
class BaseApplication extends \TYPO3\Surf\Domain\Model\Application {

	/**
	 * Register tasks for the base application
	 *
	 * The base application performs the following tasks:
	 *
	 * Initialize stage:
	 *   - Create directories for release structure
	 *
	 * Update stage:
	 *   - Perform Git checkout (and pass on sha1 / tag or branch option from application to the task)
	 *
	 * Switch stage:
	 *   - Symlink the current and previous release
	 *
	 * Cleanup stage:
	 *   - Clean up old releases
	 *
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function registerTasks(Workflow $workflow, Deployment $deployment) {
			// Forward deprecated options for backwards compatibility
		if ($this->hasOption('git-checkout-sha1')) {
			$this->setOption('typo3.surf:gitcheckout[sha1]', $this->getOption('git-checkout-sha1'));
		}
		if ($this->hasOption('git-checkout-tag')) {
			$this->setOption('typo3.surf:gitcheckout[tag]', $this->getOption('git-checkout-tag'));
		}
		if ($this->hasOption('git-checkout-branch')) {
			$this->setOption('typo3.surf:gitcheckout[branch]', $this->getOption('git-checkout-branch'));
		}

		$workflow->setTaskOptions(
			'typo3.surf:gitcheckout',
			array(
				'sha1' => $this->hasOption('git-checkout-sha1') ? $this->getOption('git-checkout-sha1') : NULL,
				'tag' => $this->hasOption('git-checkout-tag') ? $this->getOption('git-checkout-tag') : NULL,
				'branch' => $this->hasOption('git-checkout-branch') ? $this->getOption('git-checkout-branch') : NULL
			));

		$workflow
			->addTask('typo3.surf:createdirectories', 'initialize', $this)
			->addTask('typo3.surf:gitcheckout', 'update', $this)
			->addTask('typo3.surf:symlinkrelease', 'switch', $this)
			->addTask('typo3.surf:cleanupreleases', 'cleanup', $this);
	}

}
?>