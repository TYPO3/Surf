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

		$workflow
			->addTask('typo3.surf:createdirectories', 'initialize', $this)
			->addTask('typo3.surf:gitcheckout', 'update', $this)
			->addTask('typo3.surf:symlinkrelease', 'switch', $this)
			->addTask('typo3.surf:cleanupreleases', 'cleanup', $this);

		$workflow
			->afterTask('typo3.surf:createdirectories', 'typo3.surf:generic:createDirectories', $this)
			->afterTask('typo3.surf:gitcheckout', 'typo3.surf:generic:createSymlinks', $this);
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
	 * Returns all symlinks to be created.
	 *
	 * @return array
	 */
	public function getSymlinks() {
		return $this->symlinks;
	}

	/**
	 * Register an additional symlink to be created.
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
	 * Register an array of additonal symlinks to be created.
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
	 * Override all directories to be created.
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
	 * Returns all directories to be created.
	 *
	 * @return array
	 */
	public function getDirectories() {
		return $this->directories;
	}

	/**
	 * Register an additional diretory to be created.
	 *
	 * @param string $path
	 * @return \TYPO3\Surf\Application\BaseApplication
	 */
	public function addDirectory($path) {
		$this->directories[] = $path;
		return $this;
	}

	/**
	 * Register an array of additonal directories to be created.
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
}
?>