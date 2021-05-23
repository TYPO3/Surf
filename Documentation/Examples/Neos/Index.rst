.. -*- coding: utf-8 -*- with BOM.
.. include:: ../../Includes.txt
.. index:: Neos, Flow

===========================
How to deploy Neos websites
===========================

If you would like to deploy a Neos website a good starting point is to use the Neos Application class provided by Surf::

   <?php
   /** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

   $node = new \TYPO3\Surf\Domain\Model\Node('production');
   $node
      ->setHostname('my.node.com')
      ->setDeploymentPath('/var/www/vhosts/my.node.com')
      ->setOption('username', 'myuser');

   $application = new \TYPO3\Surf\Application\Neos\Neos('My Node');
   $application
      ->setOption('keepReleases', 3)
      ->setOption('composerCommandPath', 'composer')
      ->setOption('repositoryUrl', '<my repository url>')
      ->setOption('branch', 'master')
      ->setOption('updateMethod', null)
      ->setOption('baseUrl', 'https://my.node.com')
      ->setOption('flushCacheList', [
          'Neos_Fusion_Content',
          'Neos_Neos_Fusion'
      ])
      ->addNode($node);

   $deployment
      ->addApplication($application)
      ->onInitialize(
         function () use ($deployment, $application) {
               $deployment->getWorkflow()
                  ->beforeStage('transfer', \TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask::class)
                  ->afterStage('switch', \TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask::class)
                  ->afterStage('switch', \TYPO3\Surf\Task\Neos\Flow\FlushCacheListTask::class);
         }
      );

