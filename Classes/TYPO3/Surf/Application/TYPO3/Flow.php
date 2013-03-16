<?php
namespace TYPO3\Surf\Application\TYPO3;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Model\Deployment;

/**
 * A TYPO3 Flow application template
* @TYPO3\Flow\Annotations\Proxy(false)
 */
class Flow extends \TYPO3\Surf\Application\BaseApplication {

	/**
	 * The production context
	 * @var string
	 */
	protected $context = 'Production';

	/**
	 * The TYPO3 Flow major and minor version of this application
	 * @var string
	 */
	protected $version = '2.0';

	/**
	 * Constructor
	 */
	public function __construct($name = 'TYPO3 Flow') {
		parent::__construct($name);
		$this->options = array_merge($this->options, array(
			'updateMethod' => 'composer'
		));
	}

	/**
	 * Register tasks for this application
	 *
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function registerTasks(Workflow $workflow, Deployment $deployment) {
		parent::registerTasks($workflow, $deployment);

		$workflow
			->addTask('typo3.surf:typo3:flow:createdirectories', 'initialize', $this)
			->afterStage('update', array(
				'typo3.surf:typo3:flow:symlinkdata',
				'typo3.surf:typo3:flow:symlinkconfiguration',
				'typo3.surf:typo3:flow:copyconfiguration'
			), $this)
			->addTask('typo3.surf:typo3:flow:migrate', 'migrate', $this);
	}

	/**
	 * Register local composer install task for packageMethod "git" after stage "package"
	 *
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param string $packageMethod
	 * @return void
	 */
	protected function registerTasksForPackageMethod(Workflow $workflow, $packageMethod) {
		parent::registerTasksForPackageMethod($workflow, $packageMethod);

		$workflow->defineTask('typo3.surf:composer:localinstall', 'typo3.surf:composer:install', array(
			'nodeName' => 'localhost',
			'useApplicationWorkspace' => TRUE
		));

		if ($packageMethod === 'git') {
			$workflow->afterStage('package', 'typo3.surf:composer:localinstall', $this);
		}
	}

	/**
	 * Add support for updateMethod "composer"
	 *
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param string $updateMethod
	 * @return void
	 */
	protected function registerTasksForUpdateMethod(Workflow $workflow, $updateMethod) {
		switch ($updateMethod) {
			case 'composer':
				$workflow->addTask('typo3.surf:composer:install', 'update', $this);
				break;
			default:
				parent::registerTasksForUpdateMethod($workflow, $updateMethod);
				break;
		}
	}

	/**
	 * Set the application production context
	 *
	 * @param string $context
	 * @return \TYPO3\Surf\Application\TYPO3\Flow
	 */
	public function setContext($context) {
		$this->context = trim($context);
		return $this;
	}

	/**
	 * Get the application production context
	 *
	 * @return string
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * @param string $version
	 */
	public function setVersion($version) {
		$this->version = $version;
	}

	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * Get the directory name for build essentials (e.g. to run unit tests)
	 *
	 * The value depends on the Flow version of the application.
	 *
	 * @return string
	 */
	public function getBuildEssentialsDirectoryName() {
		if ($this->getVersion() <= '1.1') {
			return 'Common';
		} else {
			return 'BuildEssentials';
		}
	}

	/**
	 * Get the name of the Flow script (flow or flow3)
	 *
	 * The value depends on the Flow version of the application.
	 *
	 * @return string
	 */
	public function getFlowScriptName() {
		if ($this->getVersion() <= '1.1') {
			return 'flow3';
		} else {
			return 'flow';
		}
	}

}
?>