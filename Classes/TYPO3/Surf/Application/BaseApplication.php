<?php
namespace TYPO3\Surf\Application;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
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
	 * Symlinks, which should be created for each release.
	 *
	 * @see \TYPO3\Surf\Task\Generic\CreateSymlinksTask
	 * @var array
	 */
	protected $symlinks = array();

	/**
	 * Directories which should be created on deployment. E.g. shared folders.
	 *
	 * @var array
	 */
	protected $directories = array();

	/**
	 * Basic application specific options
	 *
	 *   packageMethod: How to prepare the application assets (code and files) locally before transfer
	 *
	 *     "git" Make a local git checkout and transfer files to the server
	 *     none  Default, do not prepare anything locally
	 *
	 *   transferMethod: How to transfer the application assets to a node
	 *
	 *     "git" Make a checkout of the application assets remotely on the node
	 *
	 *   updateMethod: How to prepare and update the application assets on the node after transfer
	 *
	 * @var array
	 */
	protected $options = array(
		'packageMethod' => NULL,
		'transferMethod' => 'git',
		'updateMethod' => NULL
	);

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
		$this->setDeprecatedTaskOptions($deployment);

		$workflow->setTaskOptions(
			'typo3.surf:generic:createDirectories',
			array(
				'directories' => $this->getDirectories()
			));
		$workflow->setTaskOptions(
			'typo3.surf:generic:createSymlinks',
			array(
				'symlinks' => $this->getSymlinks()
			));

		if ($this->hasOption('packageMethod')) {
			$this->registerTasksForPackageMethod($workflow, $this->getOption('packageMethod'));
		}

		if ($this->hasOption('transferMethod')) {
			$this->registerTasksForTransferMethod($workflow, $this->getOption('transferMethod'));
		}

		$workflow->afterStage('transfer', 'typo3.surf:generic:createSymlinks', $this);

		if ($this->hasOption('updateMethod')) {
			$this->registerTasksForUpdateMethod($workflow, $this->getOption('updateMethod'));
		}

		// TODO Define tasks for local shell task and local git checkout

		$workflow
			->addTask('typo3.surf:createdirectories', 'initialize', $this)
				->afterTask('typo3.surf:createdirectories', 'typo3.surf:generic:createDirectories', $this)
			->addTask('typo3.surf:symlinkrelease', 'switch', $this)
			->addTask('typo3.surf:cleanupreleases', 'cleanup', $this);
	}

	/**
	 * Override all symlinks to be created with the given array of symlinks.
	 *
	 * @param array $symlinks
	 * @return \TYPO3\Surf\Application\BaseApplication
	 * @see addSymlinks()
	 */
	public function setSymlinks(array $symlinks) {
		$this->symlinks = $symlinks;
		return $this;
	}

	/**
	 * Get all symlinks to be created for the application
	 *
	 * @return array
	 */
	public function getSymlinks() {
		return $this->symlinks;
	}

	/**
	 * Register an additional symlink to be created for the application
	 *
	 * @param string $linkPath The link to create
	 * @param string $sourcePath The file/directory where the link should point to
	 * @return \TYPO3\Surf\Application\BaseApplication
	 */
	public function addSymlink($linkPath, $sourcePath) {
		$this->symlinks[$linkPath] = $sourcePath;
		return $this;
	}

	/**
	 * Register an array of additonal symlinks to be created for the application
	 *
	 * @param array $symlinks
	 * @return \TYPO3\Surf\Application\BaseApplication
	 * @see setSymlinks()
	 */
	public function addSymlinks(array $symlinks) {
		foreach ($symlinks as $linkPath => $sourcePath) {
			$this->addSymlink($linkPath, $sourcePath);
		}
		return $this;
	}

	/**
	 * Override all directories to be created for the application
	 *
	 * @param array $directories
	 * @return \TYPO3\Surf\Application\BaseApplication
	 * @see addDIrectories()
	 */
	public function setDirectories(array $directories) {
		$this->directories = $directories;
		return $this;
	}

	/**
	 * Get directories to be created for the application
	 *
	 * @return array
	 */
	public function getDirectories() {
		return $this->directories;
	}

	/**
	 * Register an additional diretory to be created for the application
	 *
	 * @param string $path
	 * @return \TYPO3\Surf\Application\BaseApplication
	 */
	public function addDirectory($path) {
		$this->directories[] = $path;
		return $this;
	}

	/**
	 * Register an array of additonal directories to be created for the application
	 *
	 * @param array $directories
	 * @return \TYPO3\Surf\Application\BaseApplication
	 * @see setDirectories()
	 */
	public function addDirectories(array $directories) {
		foreach ($directories as $path) {
			$this->addDirectory($path);
		}
		return $this;
	}

	/**
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param string $packageMethod
	 * @return void
	 */
	protected function registerTasksForPackageMethod(Workflow $workflow, $packageMethod) {
		switch ($packageMethod) {
			case 'git':
				$workflow->addTask('typo3.surf:package:git', 'package', $this);
				break;
		}
	}

	/**
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param string $transferMethod
	 * @return void
	 */
	protected function registerTasksForTransferMethod(Workflow $workflow, $transferMethod) {
		switch ($transferMethod) {
			case 'git':
				$workflow->addTask('typo3.surf:gitCheckout', 'transfer', $this);
				break;
			case 'rsync':
				$workflow->addTask('typo3.surf:transfer:rsync', 'transfer', $this);
				break;
			case 'scp':
				// TODO
				break;
			case 'ftp':
				// TODO
				break;
		}
	}

	/**
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param string $updateMethod
	 * @return void
	 */
	protected function registerTasksForUpdateMethod(Workflow $workflow, $updateMethod) {

	}

	/**
	 * Forward deprecated options for backwards compatibility
	 *
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return void
	 */
	protected function setDeprecatedTaskOptions(Deployment $deployment) {
		if ($this->hasOption('git-checkout-sha1')) {
			$deployment->getLogger()->log('Option "git-checkout-sha1" is deprecated and will be removed before Surf 1.0.0. Set option "typo3.surf:gitCheckout[sha1]" instead.', LOG_NOTICE);
			$this->setOption('typo3.surf:gitCheckout[sha1]', $this->getOption('git-checkout-sha1'));
		}
		if ($this->hasOption('git-checkout-tag')) {
			$deployment->getLogger()->log('Option "git-checkout-tag" is deprecated and will be removed before Surf 1.0.0. Set option "typo3.surf:gitCheckout[tag]" instead.', LOG_NOTICE);
			$this->setOption('typo3.surf:gitCheckout[tag]', $this->getOption('git-checkout-tag'));
		}
		if ($this->hasOption('git-checkout-branch')) {
			$deployment->getLogger()->log('Option "git-checkout-branch" is deprecated and will be removed before Surf 1.0.0. Set option "typo3.surf:gitCheckout[branch]" instead.', LOG_NOTICE);
			$this->setOption('typo3.surf:gitCheckout[branch]', $this->getOption('git-checkout-branch'));
		}
	}
}
?>