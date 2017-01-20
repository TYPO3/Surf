<?php
namespace TYPO3\Surf\Application\Neos;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * An "application" which does bundle Neos Flow or similar distributions.
 *
 */
class FlowDistribution extends Flow
{
    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('Neos Flow Distribution');
        $this->setOption('tagRecurseIntoSubmodules', true);
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

        $this->checkIfMandatoryOptionsExist();
        $this->buildConfiguration();
        $this->defineTasks($workflow, $deployment);

        if ($this->getOption('enableTests') !== false) {
            $workflow
                ->addTask(array(
                    'TYPO3\\Surf\\Task\\Neos\\Flow\\UnitTestTask',
                    'TYPO3\\Surf\\Task\\Neos\\Flow\\FunctionalTestTask'
                ), 'test', $this);
        }

        $workflow->addTask(array(
                'TYPO3\\Surf\\DefinedTask\\CreateZipDistributionTask',
                'TYPO3\\Surf\\DefinedTask\\CreateTarGzDistributionTask',
                'TYPO3\\Surf\\DefinedTask\\CreateTarBz2DistributionTask',
            ), 'cleanup', $this);

        if ($this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === true) {
            $workflow->addTask('TYPO3\\Surf\\Task\\SourceforgeUploadTask', 'cleanup', $this);
        }
        if ($this->hasOption('releaseHost')) {
            $workflow->addTask('TYPO3\\Surf\\Task\\Release\\PrepareReleaseTask', 'initialize', $this);
            $workflow->addTask('TYPO3\\Surf\\Task\\Release\\ReleaseTask', 'cleanup', $this);
        }
        if ($this->hasOption('releaseHost') && $this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === true) {
            $workflow->addTask('TYPO3\\Surf\\Task\\Release\\AddDownloadTask', 'cleanup', $this);
        }
        if ($this->hasOption('createTags') && $this->getOption('createTags') === true) {
            $workflow->addTask('TYPO3\\Surf\\Task\\Git\\TagTask', 'cleanup', $this);
            if ($this->hasOption('TYPO3\\Surf\\DefinedTask\\Git\\PushTagsTask') && $this->getOption('TYPO3\\Surf\\DefinedTask\\Git\\PushTagsTask') === true) {
                $workflow->afterTask('TYPO3\\Surf\\Task\\Git\\TagTask', 'TYPO3\\Surf\\DefinedTask\\Git\\PushTagsTask', $this);
            }
        }

        $workflow->removeTask('TYPO3\\Surf\\Task\\Neos\\Flow\\MigrateTask');
    }

    /**
     * Check if all necessary options to run are set
     *
     * @return void
     * @throws InvalidConfigurationException
     */
    protected function checkIfMandatoryOptionsExist()
    {
        if (!$this->hasOption('version')) {
            throw new InvalidConfigurationException('"version" option needs to be defined. Example: 1.0.0-beta2', 1314187396);
        }
        if (!$this->hasOption('projectName')) {
            throw new InvalidConfigurationException('"projectName" option needs to be defined. Example: Neos Flow', 1314187397);
        }

        if ($this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === true) {
            if (!$this->hasOption('sourceforgeProjectName')) {
                throw new InvalidConfigurationException('"sourceforgeProjectName" option needs to be specified', 1314187402);
            }
            if (!$this->hasOption('sourceforgePackageName')) {
                throw new InvalidConfigurationException('"sourceforgePackageName" option needs to be specified', 1314187406);
            }
            if (!$this->hasOption('sourceforgeUserName')) {
                throw new InvalidConfigurationException('"sourceforgeUserName" option needs to be specified', 1314187407);
            }
        }

        if ($this->hasOption('releaseHost')) {
            if (!$this->hasOption('releaseHostSitePath')) {
                throw new InvalidConfigurationException('"releaseHostSitePath" option needs to be specified', 1321545975);
            }
        }
        if ($this->hasOption('releaseHost') && $this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === true) {
            if (!$this->hasOption('releaseDownloadLabel')) {
                throw new InvalidConfigurationException('"releaseDownloadLabel" option needs to be specified', 1321545965);
            }
            if (!$this->hasOption('releaseDownloadUriPattern')) {
                throw new InvalidConfigurationException('"releaseDownloadUriPattern" option needs to be specified', 1321545985);
            }
        }
    }

    /**
     * Build configuration which we need later into $this->configuration
     *
     * @return void
     */
    protected function buildConfiguration()
    {
        $versionAndProjectName = sprintf('%s-%s', str_replace(' ', '_', $this->getOption('projectName')), $this->getOption('version'));
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
    protected function defineTasks(Workflow $workflow, Deployment $deployment)
    {
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

        $workflow->defineTask('TYPO3\\Surf\\DefinedTask\\CreateZipDistributionTask', 'TYPO3\\Surf\\Task\\CreateArchiveTask', array_merge($baseArchiveConfiguration, array(
            'targetFile' => $this->configuration['zipFile']
        )));

        $workflow->defineTask('TYPO3\\Surf\\DefinedTask\\CreateTarGzDistributionTask', 'TYPO3\\Surf\\Task\\CreateArchiveTask', array_merge($baseArchiveConfiguration, array(
            'targetFile' => $this->configuration['tarGzFile'],
        )));

        $workflow->defineTask('TYPO3\\Surf\\DefinedTask\\CreateTarBz2DistributionTask', 'TYPO3\\Surf\\Task\\CreateArchiveTask', array_merge($baseArchiveConfiguration, array(
            'targetFile' => $this->configuration['tarBz2File'],
        )));

        if ($this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === true) {
            $workflow->defineTask('TYPO3\\Surf\\Task\\SourceforgeUploadTask', 'TYPO3\\Surf\\Task\\SourceforgeUploadTask', array(
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

        if ($this->hasOption('releaseHost')) {
            $workflow->defineTask('TYPO3\\Surf\\Task\\Release\\PrepareReleaseTask', 'TYPO3\\Surf\\Task\\Release\\PrepareReleaseTask', array(
                'releaseHost' =>  $this->getOption('releaseHost'),
                'releaseHostSitePath' => $this->getOption('releaseHostSitePath'),
                'releaseHostLogin' =>  $this->hasOption('releaseHostLogin') ? $this->getOption('releaseHostLogin') : null,
                'productName' => $this->getOption('projectName'),
                'version' => $this->getOption('version'),
            ));
            $workflow->defineTask('TYPO3\\Surf\\Task\\Release\\ReleaseTask', 'TYPO3\\Surf\\Task\\Release\\ReleaseTask', array(
                'releaseHost' =>  $this->getOption('releaseHost'),
                'releaseHostSitePath' => $this->getOption('releaseHostSitePath'),
                'releaseHostLogin' =>  $this->hasOption('releaseHostLogin') ? $this->getOption('releaseHostLogin') : null,
                'productName' => $this->getOption('projectName'),
                'version' => $this->getOption('version'),
                'changeLogUri' =>  $this->hasOption('changeLogUri') ? $this->getOption('changeLogUri') : null,
            ));
        }

        if ($this->hasOption('releaseHost') && $this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === true) {
            $workflow->defineTask('TYPO3\\Surf\\Task\\Release\\AddDownloadTask', 'TYPO3\\Surf\\Task\\Release\\AddDownloadTask', array(
                'releaseHost' =>  $this->getOption('releaseHost'),
                'releaseHostSitePath' => $this->getOption('releaseHostSitePath'),
                'releaseHostLogin' =>  $this->hasOption('releaseHostLogin') ? $this->getOption('releaseHostLogin') : null,
                'productName' => $this->getOption('projectName'),
                'version' => $this->getOption('version'),
                'label' => $this->getOption('releaseDownloadLabel'),
                'downloadUriPattern' => $this->getOption('releaseDownloadUriPattern'),
                'files' => array(
                    $this->configuration['zipFile'],
                    $this->configuration['tarGzFile'],
                    $this->configuration['tarBz2File'],
                )
            ));
        }

        $workflow->defineTask('TYPO3\\Surf\\Task\\Git\\TagTask', 'TYPO3\\Surf\\Task\\Git\\TagTask', array(
            'tagName' => $this->configuration['versionAndProjectName'],
            'description' => 'Tag distribution with tag ' . $this->configuration['versionAndProjectName'],
            'recurseIntoSubmodules' => $this->getOption('tagRecurseIntoSubmodules')
        ));

        $workflow->defineTask('TYPO3\\Surf\\DefinedTask\\Git\\PushTagsTask', 'TYPO3\\Surf\\Task\\Git\\PushTask', array(
            'remote' => 'origin',
            'refspec' => $this->configuration['versionAndProjectName'] . ':refs/tags/' . $this->configuration['versionAndProjectName'],
            'recurseIntoSubmodules' => $this->getOption('tagRecurseIntoSubmodules')
        ));
    }
}
