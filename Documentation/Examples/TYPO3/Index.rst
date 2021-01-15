.. -*- coding: utf-8 -*- with BOM.
.. include:: ../../Includes.txt
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
