<?php
namespace TYPO3\Surf\Application\Neos;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\CreateArchiveTask;
use TYPO3\Surf\Task\Git\PushTask;
use TYPO3\Surf\Task\Git\TagTask;
use TYPO3\Surf\Task\Neos\Flow\FunctionalTestTask;
use TYPO3\Surf\Task\Neos\Flow\MigrateTask;
use TYPO3\Surf\Task\Neos\Flow\UnitTestTask;
use TYPO3\Surf\Task\Release\AddDownloadTask;
use TYPO3\Surf\Task\Release\PrepareReleaseTask;
use TYPO3\Surf\Task\Release\ReleaseTask;
use TYPO3\Surf\Task\SourceforgeUploadTask;

/**
 * An "application" which does bundle Neos Flow or similar distributions.
 */
class FlowDistribution extends Flow
{
    /**
     * @var array
     */
    protected $configuration = [];

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
     */
    public function registerTasks(Workflow $workflow, Deployment $deployment)
    {
        parent::registerTasks($workflow, $deployment);

        $this->checkIfMandatoryOptionsExist();
        $this->buildConfiguration();
        $this->defineTasks($workflow, $deployment);

        if ($this->getOption('enableTests') !== false) {
            $workflow
                ->addTask([
                    UnitTestTask::class,
                    FunctionalTestTask::class
                ], 'test', $this);
        }

        $workflow->addTask([
                'TYPO3\\Surf\\DefinedTask\\CreateZipDistributionTask',
                'TYPO3\\Surf\\DefinedTask\\CreateTarGzDistributionTask',
                'TYPO3\\Surf\\DefinedTask\\CreateTarBz2DistributionTask',
            ], 'cleanup', $this);

        if ($this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === true) {
            $workflow->addTask(SourceforgeUploadTask::class, 'cleanup', $this);
        }
        if ($this->hasOption('releaseHost')) {
            $workflow->addTask(PrepareReleaseTask::class, 'initialize', $this);
            $workflow->addTask(ReleaseTask::class, 'cleanup', $this);
        }
        if ($this->hasOption('releaseHost') && $this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === true) {
            $workflow->addTask(AddDownloadTask::class, 'cleanup', $this);
        }
        if ($this->hasOption('createTags') && $this->getOption('createTags') === true) {
            $workflow->addTask(TagTask::class, 'cleanup', $this);
            if ($this->hasOption('TYPO3\\Surf\\DefinedTask\\Git\\PushTagsTask') && $this->getOption('TYPO3\\Surf\\DefinedTask\\Git\\PushTagsTask') === true) {
                $workflow->afterTask(TagTask::class, 'TYPO3\\Surf\\DefinedTask\\Git\\PushTagsTask', $this);
            }
        }

        $workflow->removeTask(MigrateTask::class);
    }

    /**
     * Check if all necessary options to run are set
     *
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

        if ($this->hasOption('releaseHost') && !$this->hasOption('releaseHostSitePath')) {
            throw new InvalidConfigurationException('"releaseHostSitePath" option needs to be specified', 1321545975);
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
     */
    protected function defineTasks(Workflow $workflow, Deployment $deployment)
    {
        $excludePatterns = [
            '.git*',
            'Data/*',
            'Web/_Resources/*',
            'Build/Reports',
            './Cache',
            'Configuration/PackageStates.php'
        ];

        $baseArchiveConfiguration = [
            'sourceDirectory' => $deployment->getApplicationReleasePath($this),
            'baseDirectory' => $this->configuration['versionAndProjectName'],
            'exclude' => $excludePatterns
        ];

        $workflow->defineTask('TYPO3\\Surf\\DefinedTask\\CreateZipDistributionTask', CreateArchiveTask::class, array_merge($baseArchiveConfiguration, [
            'targetFile' => $this->configuration['zipFile']
        ]));

        $workflow->defineTask('TYPO3\\Surf\\DefinedTask\\CreateTarGzDistributionTask', CreateArchiveTask::class, array_merge($baseArchiveConfiguration, [
            'targetFile' => $this->configuration['tarGzFile'],
        ]));

        $workflow->defineTask('TYPO3\\Surf\\DefinedTask\\CreateTarBz2DistributionTask', CreateArchiveTask::class, array_merge($baseArchiveConfiguration, [
            'targetFile' => $this->configuration['tarBz2File'],
        ]));

        if ($this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === true) {
            $workflow->defineTask(SourceforgeUploadTask::class, SourceforgeUploadTask::class, [
                'sourceforgeProjectName' => $this->getOption('sourceforgeProjectName'),
                'sourceforgePackageName' => $this->getOption('sourceforgePackageName'),
                'sourceforgeUserName' => $this->getOption('sourceforgeUserName'),
                'version' => $this->getOption('version'),
                'files' => [
                    $this->configuration['zipFile'],
                    $this->configuration['tarGzFile'],
                    $this->configuration['tarBz2File'],
                ]
            ]);
        }

        if ($this->hasOption('releaseHost')) {
            $workflow->defineTask(PrepareReleaseTask::class, PrepareReleaseTask::class, [
                'releaseHost' =>  $this->getOption('releaseHost'),
                'releaseHostSitePath' => $this->getOption('releaseHostSitePath'),
                'releaseHostLogin' =>  $this->hasOption('releaseHostLogin') ? $this->getOption('releaseHostLogin') : null,
                'productName' => $this->getOption('projectName'),
                'version' => $this->getOption('version'),
            ]);
            $workflow->defineTask(ReleaseTask::class, ReleaseTask::class, [
                'releaseHost' =>  $this->getOption('releaseHost'),
                'releaseHostSitePath' => $this->getOption('releaseHostSitePath'),
                'releaseHostLogin' =>  $this->hasOption('releaseHostLogin') ? $this->getOption('releaseHostLogin') : null,
                'productName' => $this->getOption('projectName'),
                'version' => $this->getOption('version'),
                'changeLogUri' =>  $this->hasOption('changeLogUri') ? $this->getOption('changeLogUri') : null,
            ]);
        }

        if ($this->hasOption('releaseHost') && $this->hasOption('enableSourceforgeUpload') && $this->getOption('enableSourceforgeUpload') === true) {
            $workflow->defineTask(AddDownloadTask::class, AddDownloadTask::class, [
                'releaseHost' =>  $this->getOption('releaseHost'),
                'releaseHostSitePath' => $this->getOption('releaseHostSitePath'),
                'releaseHostLogin' =>  $this->hasOption('releaseHostLogin') ? $this->getOption('releaseHostLogin') : null,
                'productName' => $this->getOption('projectName'),
                'version' => $this->getOption('version'),
                'label' => $this->getOption('releaseDownloadLabel'),
                'downloadUriPattern' => $this->getOption('releaseDownloadUriPattern'),
                'files' => [
                    $this->configuration['zipFile'],
                    $this->configuration['tarGzFile'],
                    $this->configuration['tarBz2File'],
                ]
            ]);
        }

        $workflow->defineTask(TagTask::class, TagTask::class, [
            'tagName' => $this->configuration['versionAndProjectName'],
            'description' => 'Tag distribution with tag ' . $this->configuration['versionAndProjectName'],
            'recurseIntoSubmodules' => $this->getOption('tagRecurseIntoSubmodules')
        ]);

        $workflow->defineTask('TYPO3\\Surf\\DefinedTask\\Git\\PushTagsTask', PushTask::class, [
            'remote' => 'origin',
            'refspec' => $this->configuration['versionAndProjectName'] . ':refs/tags/' . $this->configuration['versionAndProjectName'],
            'recurseIntoSubmodules' => $this->getOption('tagRecurseIntoSubmodules')
        ]);
    }
}
