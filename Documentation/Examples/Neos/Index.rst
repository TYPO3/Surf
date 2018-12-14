.. -*- coding: utf-8 -*- with BOM.
.. include:: ../../Includes.txt

===========================
How to deploy Neos websites
===========================

If you would like to deploy a Neos Application a good starting point is to use Neos Application class provided by Surf::

   $node = new TYPO3\Surf\Domain\Model\Node('production');
   $node
      ->setHostname('my.node.com')
      ->setOption('username', 'myuser');
   $application = new TYPO3\Surf\Application\Neos\Neos('My Node');
   $application
      ->setOption('keepReleases', 3)
      ->setOption('composerCommandPath', 'composer')
      ->setOption('repositoryUrl', '<my repository url>')
      ->setOption('branch', 'master')
      ->setOption('updateMethod', null)
      ->setOption('baseUrl', 'https://my.node.com')
      ->setDeploymentPath('/var/www/vhosts/my.node.com')
      ->addNode($node);
   /** @var $deployment TYPO3\Surf\Domain\Model\Deployment "injected" into this script from Surf */
   $deployment
      ->addApplication($application)
      ->onInitialize(
         function () use ($deployment, $application) {
               $deployment->getWorkflow()
                  ->beforeStage('transfer', TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask::class)
                  ->afterStage('switch', TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask::class)
                  ->defineTask(
                     'Task:FlushFusionContentCache',
                     TYPO3\Surf\Task\Neos\Flow\RunCommandTask::class,
                     [
                           'command' => 'flow:cache:flushone --identifier Neos_Fusion_Content'
                     ]
                  )->forStage('switch', 'Task:FlushFusionContentCache')
                  ->defineTask(
                     'Task:FlushFusionCache',
                     TYPO3\Surf\Task\Neos\Flow\RunCommandTask::class,
                     [
                           'command' => 'flow:cache:flushone --identifier Neos_Neos_Fusion'
                     ]
                  )->forStage('switch', 'Task:FlushFusionCache');
         }
      );
