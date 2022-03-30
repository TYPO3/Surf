.. include:: /Includes.rst.txt

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


Application options
-------------------

This is a (so far) incomplete list of options for :php:`\TYPO3\Surf\Application\BaseApplication`.

Help documenting them all! Click the button "Edit on Github".

.. contents::
   :local:
   :depth: 2

Git-related
~~~~~~~~~~~


repositoryUrl
"""""""""""""

.. rst-class:: dl-parameters

repositoryUrl
   :sep:`|` :aspect:`Data type:` string
   :sep:`|`

   Git will clone from this URL, usually in the `package` stage.

   Any URL understood by Git can be used (http, ssh, file).

   **Example:** ::

      $application
        ->setOption('repositoryUrl', 'file://' . dirname(realpath(__DIR__ . '/../..')))
        // ...


branch
""""""

.. rst-class:: dl-parameters

branch
   :sep:`|` :aspect:`Data type:` string
   :sep:`|`

   A branch name.

   **Example:** ::

      $releaseChannel = 'live';

      $application
        ->setOption('branch', 'release/' . $releaseChannel)))
        // ...



tag
"""

.. rst-class:: dl-parameters

tag
   :sep:`|` :aspect:`Data type:` string
   :sep:`|`

   A tag name or tag glob pattern that is understood by `git ls-remote <https://git-scm.com/docs/git-ls-remote.html>`__.

   Surf uses git (:bash:`ls-remote --sort=version`) to sort the results *versiony* and return the highest matching tag.

   **Example:** ::

      $releaseChannel = 'live';

      $application
        // this would checkout the commit with highest tag matching "live-*",
        // for example live-1.2.3
        ->setOption('tag', $releaseChannel . '-*')))
        // ...


Special Use Cases
-----------------


Applying Cherry-Picks to Git Repositories: Post-Checkout commands
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When you want to execute some commands directly after checkout, such as cherry-picking not-yet-committed bugfixes, you can set the gitPostCheckoutCommands option on the application, being a two-dimensional array.

The key contains the path where the command shall execute, and the value is another array containing the commands themselves.

Example::

   $application->setOption('gitPostCheckoutCommands', [
      'Packages/Framework/Neos.Flow/' => [
         'git fetch https://github.com/neos/flow-development-collection.git refs/heads/somefix',
         'git cherry-pick FETCH_HEAD'
      ]
   ]);

