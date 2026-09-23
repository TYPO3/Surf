.. include:: /Includes.rst.txt
.. index:: TYPO3

============================
How to deploy TYPO3 websites
============================

If you would like to deploy a TYPO3 website a good starting point is to use TYPO3\CMS Application class provided by Surf::

   <?php
   /** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

   $node = new \TYPO3\Surf\Domain\Model\Node('my.node.com');
   $node
       ->setHostname($node->getName())
       ->setDeploymentPath('/httpdocs')
       ->setOption('username', 'myuser')
       ->setOption('phpBinaryPathAndFilename', '/usr/local/bin/php_cli');

   $application = new \TYPO3\Surf\Application\TYPO3\CMS();
   $application
       ->setOption('baseUrl', 'https://my.node.com/')
       ->setOption('webDirectory', 'public')
       ->setOption('symlinkDataFolders', ['fileadmin'])
       ->setOption('repositoryUrl', 'file://' . dirname(__DIR__))
       ->setOption('keepReleases', 3)
       ->setOption('composerCommandPath', 'composer')
       ->setOption('rsyncExcludes', [
           '.ddev',
           '.git',
           $application->getOption('webDirectory') . '/fileadmin',
           'packages/**.sass'
       ])
       ->addSymlink($application->getOption('webDirectory') . '/typo3conf/LocalConfiguration.php', '../../../../shared/Configuration/LocalConfiguration.php')
       ->addNode($node);

   $deployment
       ->addApplication($application)
       ->onInitialize(
           function () use ($deployment, $application) {
               $deployment->getWorkflow()
                   ->beforeTask(\TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask::class, \TYPO3\Surf\Task\TYPO3\CMS\CompareDatabaseTask::class, $application)
                   ->beforeStage('transfer', \TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask::class, $application)
                   ->afterStage('switch', \TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask::class, $application);
           }
       );

Configuration file in TYPO3 12 and later
========================================

Since TYPO3 12 the configuration lives in :file:`config/system/settings.php`
instead of :file:`typo3conf/LocalConfiguration.php`. Replace the symlink of the
example above with::

   $application->addSymlink('config/system/settings.php', '../../../../shared/Configuration/settings.php');

Publishing extension assets (TYPO3 14.2 and later)
==================================================

For how TYPO3 publishes extension assets, see
`Assets <https://docs.typo3.org/m/typo3/tutorial-sitepackage/14.3/en-us/Assets/Index.html>`__
and
`Feature #109163 <https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.2/Feature-109163-Implement-public-system-resources-publishing.html>`__.

If the Composer install runs without the complete TYPO3 configuration, skip
the publishing there and publish on the node with the
``AssetPublishTask`` instead::

   $deployment->onInitialize(
       function () use ($deployment, $application) {
           $deployment->getWorkflow()
               ->setTaskOptions('TYPO3\\Surf\\DefinedTask\\Composer\\LocalInstallTask', [
                   'composerCommandPath' => 'TYPO3_SKIP_ASSET_PUBLISH=1 composer',
               ])
               ->addTask(\TYPO3\Surf\Task\TYPO3\CMS\AssetPublishTask::class, 'migrate', $application);
       }
   );
