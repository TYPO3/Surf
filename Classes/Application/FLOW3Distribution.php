<?php
namespace TYPO3\Surf\Application;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Domain\Model\Deployment;

/**
 * An "application" which does bundles FLOW3 or similar distributions.
 *
 */
class FLOW3Distribution extends \TYPO3\Surf\Domain\Model\Application {

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct('FLOW3 Distribution');
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

		$this->checkIfMandatoryOptionsExist();
		$this->buildConfiguration();
		$this->defineTasks($workflow, $deployment);

		$workflow
			->forApplication($this, 'initialize', array(
				'typo3.surf:flow3:createdirectories'
			));

		if ($this->getOption('enableTests') !== FALSE) {
			$workflow
				->forApplication($this, 'test', array(
					'typo3.surf:flow3:unittest'
				))
				->forApplication($this, 'test', array(
					'typo3.surf:flow3:functionaltest'
				));
		}

		$workflow
			->forApplication($this, 'cleanup', array(
				'createZipDistribution',
				'createTarGzDistribution',
				'createTarBz2Distribution',
			));

		if ($this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === TRUE) {
			$workflow
				->forApplication($this, 'cleanup', array(
					'typo3.surf:sourceforgeupload'
				));
		}
		if ($this->hasOption('createTags') && $this->getOption('createTags') === TRUE) {
			$workflow
				->forApplication($this, 'cleanup', array(
					'typo3.surf:git:tag'
				));
		}
	}

	/**
	 * Check if all necessary options to run are set
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function checkIfMandatoryOptionsExist() {
		if (!$this->hasOption('version')) {
			throw new \Exception('Version needs to be defined. Example: 1.0.0-beta2', 1314187396);
		}
		if (!$this->hasOption('projectName')) {
			throw new \Exception('Project Name needs to be defined. Example: FLOW3', 1314187397);
		}

		if ($this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === TRUE) {
			if (!$this->hasOption('sourceforgeProjectName')) {
				throw new \Exception('sourceforgeProjectName option needs to be specified', 1314187402);
			}
			if (!$this->hasOption('sourceforgePackageName')) {
				throw new \Exception('sourceforgePackageName option needs to be specified', 1314187406);
			}
			if (!$this->hasOption('sourceforgeUserName')) {
				throw new \Exception('sourceforgeUserName option needs to be specified', 1314187407);
			}
		}
	}

	/**
	 * Build configuration which we need later into $this->configuration
	 *
	 * @return void
	 */
	protected function buildConfiguration() {
		$versionAndProjectName = sprintf('%s-%s', $this->getOption('projectName'), $this->getOption('version'));
		$this->configuration['versionAndProjectName'] = $versionAndProjectName;

		$this->configuration['zipFile'] = $this->getDeploymentPath() . '/buildArtifacts/' . $versionAndProjectName . '.zip';
		$this->configuration['tarGzFile'] = $this->getDeploymentPath() . '/buildArtifacts/' . $versionAndProjectName . '.tar.gz';
		$this->configuration['tarBz2File'] = $this->getDeploymentPath() . '/buildArtifacts/' . $versionAndProjectName . '.tar.bz2';
	}

	/**
	 * Configure tasks
	 *
	 * @param Workflow $workflow
	 * @param Deployment $deployment
	 * @return void
	 */
	protected function defineTasks(Workflow $workflow, Deployment $deployment) {
		$excludePatterns = array(
			'.git*',
			'Data/*',
			'Web/_Resources/*',
			'Build/Reports',
			'./Cache',
			'Configuration/PackageStates.php'
		);

		$baseArchiveConfiguration = array(
			'sourceDirectory' => $deployment->getApplicationReleasePath($this),
			'baseDirectory' => $this->configuration['versionAndProjectName'],
			'exclude' => $excludePatterns
		);

		$workflow->defineTask('createZipDistribution', 'typo3.surf:createArchive', array_merge($baseArchiveConfiguration, array(
			'targetFile' => $this->configuration['zipFile']
		)));

		$workflow->defineTask('createTarGzDistribution', 'typo3.surf:createArchive', array_merge($baseArchiveConfiguration, array(
			'targetFile' => $this->configuration['tarGzFile'],
		)));

		$workflow->defineTask('createTarBz2Distribution', 'typo3.surf:createArchive', array_merge($baseArchiveConfiguration, array(
			'targetFile' => $this->configuration['tarBz2File'],
		)));

		if ($this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === TRUE) {
			$workflow->defineTask('typo3.surf:sourceforgeupload', 'typo3.surf:sourceforgeupload', array(
				'sourceforgeProjectName' => $this->getOption('sourceforgeProjectName'),
				'sourceforgePackageName' => $this->getOption('sourceforgePackageName'),
				'sourceforgeUserName' => $this->getOption('sourceforgeUserName'),
				'version' => $this->getOption('version'),
				'files' => array(
					$this->configuration['zipFile'],
					$this->configuration['tarGzFile'],
					$this->configuration['tarBz2File'],
				)
			));
		}

		$workflow->defineTask('typo3.surf:git:tag', 'typo3.surf:git:tag', array(
			'tagName' => $this->getOption('version'),
			'description' => 'Tag distribution with tag ' . $this->getOption('version')
		));
	}
}
?>