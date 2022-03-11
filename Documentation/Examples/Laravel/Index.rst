.. -*- coding: utf-8 -*- with BOM.
.. include:: ../../Includes.txt
.. index:: TYPO3

==================================
How to deploy Laravel applications
==================================

If you would like to deploy a Laravel application a good starting point is to use Laravel Application class provided by Surf::

   <?php
   /** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

   $node = new \TYPO3\Surf\Domain\Model\Node('my.node.com');
   $node
       ->setHostname($node->getName())
       ->setOption('username', 'myuser')
       ->setOption('phpBinaryPathAndFilename', '/usr/bin/php8.0');

   $application = new \TYPO3\Surf\Application\Laravel();
   $application
       ->setDeploymentPath('/var/www/html')
       ->setOption('baseUrl', 'https://my.node.com/')
       ->setOption('repositoryUrl', 'file://' . dirname(__DIR__))
       ->setOption('keepReleases', 3)
       ->setOption('composerCommandPath', 'composer')
       ->addNode($node);

   $deployment
       ->addApplication($application)
       ->onInitialize(
           function () use ($deployment, $application) {
               $deployment->getWorkflow()
                   ->beforeStage('transfer', \TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask::class, $application)
                   ->afterStage('switch', \TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask::class, $application);
           }
       );
