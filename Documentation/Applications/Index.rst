.. -*- coding: utf-8 -*- with BOM.
.. include:: ../Includes.txt

============
Applications
============

The application is the code you want to ship. The deployment configuration has at least one application.
But you can configure as many applications as you want in one deployment configuration.
The application itself contains of one or more nodes.
You can also define within the workflow whether a task is to apply to all applications or only to a specific application.

Surf already ships with specific applications with a sensitive default configuration for the execution process (which tasks are to be called in which stage).

For example there is one application class for **TYPO3** or **Neos** shipped with Surf.
But you can create your own **specific Application** class as long as it inherits from \\TYPO3\\Surf\\Domain\\Model\\Application.

By default (as long as the application inherits from **\\TYPO3\\Surf\\Application\\BaseApplication**) an application uses **rsync** and **composer** as transfer and package method. But you can also use **git**, by adding the following configuration to your application::

   $application->setOption('transferMethod', 'git');
   $application->setOption('packageMethod', NULL);
   $application->setOption('updateMethod', NULL);

.. note:: Using rsync can speed up your deployment and doesn't require composer and git on the production server.

Applying Cherry-Picks to Git Repositories: Post-Checkout commands
-----------------------------------------------------------------

When you want to execute some commands directly after checkout, such as cherry-picking not-yet-committed bugfixes, you can set the gitPostCheckoutCommands option on the application, being a two-dimensional array.

The key contains the path where the command shall execute, and the value is another array containing the commands themselves.

Example::

   $application->setOption('gitPostCheckoutCommands', [
      'Packages/Framework/Neos.Flow/' => [
         'git fetch https://github.com/neos/flow-development-collection.git refs/heads/somefix',
         'git cherry-pick FETCH_HEAD'
      ]
   ]);

