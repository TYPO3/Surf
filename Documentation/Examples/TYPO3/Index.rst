.. -*- coding: utf-8 -*- with BOM.
.. include:: ../../Includes.txt

============================
How to deploy TYPO3 websites
============================

If you would like to deploy a TYPO3 Website a good starting point is to use TYPO3\CMS Application class provided by Surf::

   <?php
   /** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

   $node = new \TYPO3\Surf\Domain\Model\Node('production');
   $node
       ->setHostname('my.node.com')
       ->setOption('username', 'myuser')
       ->setOption('phpBinaryPathAndFilename', '/usr/local/bin/php_cli');

   $application = new \TYPO3\Surf\Application\TYPO3\CMS();
   $application
       ->setOption('composerCommandPath', 'composer')
       ->setOption('keepReleases', 3)
       ->setOption('webDirectory', 'public')
       ->setOption('repositoryUrl', 'file://' . dirname(__DIR__))
       ->setOption('symlinkDataFolders', ['fileadmin'])
       ->setOption('rsyncExcludes', [
           '.docker*',
           '.editorconfig',
           '.env*',
           '.git*',
           '.surf',
           'docker-compose.yml',
           'public/fileadmin',
       ])
       ->addNode($node)
       ->setDeploymentPath('/html')
       ->addSymlink('public/typo3conf/LocalConfiguration.php', '../../../../shared/Configuration/LocalConfiguration.php');

   $deployment
       ->addApplication($application)
       ->onInitialize(
           function () use ($deployment, $application) {
               $deployment->getWorkflow()
                   ->beforeTask(\TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask::class, \TYPO3\Surf\Task\TYPO3\CMS\CompareDatabaseTask::class)
                   // CreatePackageStatesTask is done by post-autoload-dump script and can be removed
                   // https://github.com/TYPO3/TYPO3.CMS.BaseDistribution/blob/9.x/composer.json#L38
                   ->removeTask(\TYPO3\Surf\Task\TYPO3\CMS\CreatePackageStatesTask::class, $application)
                   ->removeTask(\TYPO3\Surf\Task\TYPO3\CMS\CopyConfigurationTask::class, $application);
           }
       );
