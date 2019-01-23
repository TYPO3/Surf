.. -*- coding: utf-8 -*- with BOM.
.. include:: ../../Includes.txt

============================
How to deploy TYPO3 websites
============================

If you would like to deploy a TYPO3 Website a good starting point is to use TYPO3\CMS Application class provided by Surf::

   /** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

   $nodes['My Node'] = new \TYPO3\Surf\Domain\Model\Node('My Node');
   $nodes['My Node']->setHostname('my.node.com')
      ->setOption('username', 'myuser')
      ->setOption('phpBinaryPathAndFilename', '/usr/local/bin/php_cli');

   $appOptions = [
      'composerCommandPath' => 'composer',
      'keepReleases' => 3,
      'webDirectory' => 'public',
      'repositoryUrl' =>  'file://' . dirname(__DIR__),
      'rsyncExcludes' => [
         '.docker*',
         '.editorconfig',
         '.env*',
         '.git*',
         '.surf',
         'docker-compose.yml',
         'public/fileadmin',
         'public/uploads'
      ]
   ];

   $app = new \TYPO3\Surf\Application\TYPO3\CMS();
   $app->setNodes($nodes)
      ->setDeploymentPath('/html')
      ->addSymlink('public/typo3conf/LocalConfiguration.php', '../../../../shared/Configuration/LocalConfiguration.php');

   $app->setOptions(array_merge($app->getOptions(), $appOptions));

   $deployment->addApplication($app);

