.. -*- coding: utf-8 -*- with BOM.
.. include:: ../Includes.txt

===============
Deployment Flow
===============

If your deployment configuration is not overriding the workflow option, then you are using the default SimpleWorkflow class shipped with Surf.

The SimpleWorkflow class defines 9 stages of the deployment process which are sequentially called.
Each stage can consists of none, one or multiple tasks running one after another.

You can add your own tasks for each stage. If you like, you can also specify if your custom task is running before or after a task already defined for this stage.

In the list below you can see all the 9 steps defined by the SimpleWorkflow:

initialize
   This is normally used only for an initial deployment to an instance. At this stage you may prefill certain directories for example.

   Example Task: :doc:`\\TYPO3\\Surf\\Task\\CreateDirectoriesTask <../ApiReference/Task/CreateDirectoriesTask>`

package
   This stage is where you normally package all files and assets, which will be transferred to the next stage.

   Example Task: :doc:`\\TYPO3\\Surf\\Task\\Package\\GitTask <../ApiReference/Task/Package/GitTask>`

transfer
   Here all tasks are located which serve to transfer the assets from your local computer to the node, where the application runs.

   Example Task: :doc:`\\TYPO3\\Surf\\Task\\Transfer\\RsyncTask <../ApiReference/Task/Transfer/RsyncTask>`

update
   If necessary, the transferred assets can be updated at this stage on the foreign instance.

   Example Task: :doc:`\\TYPO3\\Surf\\Task\\TYPO3\\CMS\\SymlinkDataTask <../ApiReference/Task/TYPO3/CMS/SymlinkDataTask>`

migrate
   Here you can define tasks to do some database updates / migrations. Be careful and do not delete old tables or columns, because the old code, relying on these, is still live.

   Example Task: :doc:`\\TYPO3\\Surf\\Task\\TYPO3\\CMS\\SetUpExtensionsTask <../ApiReference/Task/TYPO3/CMS/SetUpExtensionsTask>`

finalize
   This stage is meant for tasks, that should be done short before going live, like cache warm ups and so on.

   Example Task: :doc:`\\TYPO3\\Surf\\Task\\Neos\\Flow\\PublishResourcesTask <../ApiReference/Task/Neos/Flow/PublishResourcesTask>`

test
   In the test stage you can make tests, to check if everything is fine before switching the releases.

   Example Task: :doc:`\\TYPO3\\Surf\\Task\\Test\HttpTestTask <../ApiReference/Task/Test/HttpTestTask>`

switch
   This is the crucial stage. Here the old live instance is switched with the new prepared instance. Normally the new instance is symlinked.

   Example Task: :doc:`\\TYPO3\\Surf\\Task\\SymlinkReleaseTask <../ApiReference/Task/SymlinkReleaseTask>`

cleanup
   At this stage you would cleanup old releases or remove other unused stuff.

   Example Task: :doc:`\\TYPO3\\Surf\\Task\\CleanupReleasesTask <../ApiReference/Task/CleanupReleasesTask>`


You can create your own workflow if you like. In order to do so you have to extend the abstract Workflow class.
The creation of a custom workflow is out of the scope of this chapter. Have a look at the SimpleWorkflow in oder to do so.

.. note:: But we recommend to just manipulate the stages provided by the SimpleWorkflow in order to customize your deployment flow.

Manipulate the flow
-------------------

If you like to add your own tasks to a specific stage of the flow, you can just add them the following ways::

    // Add tasks to a specific stage
    $workflow->addTask('YourTask', 'cleanup');

    // Add tasks that shall be executed after the given stage
    $workflow->afterStage('YourTask', 'cleanup');

    // Add tasks that shall be executed before the given stage
    $workflow->beforeStage('YourTask', 'cleanup');

   // Add tasks that shall be executed before the given task
   $workflow->beforeTask(CreatePackageStatesTask::class, 'YourTask');

   // Add tasks that shall be executed after the given task
   $workflow->afterTask(CreatePackageStatesTask::class, 'YourTask');

If you like to remove certain tasks from the flow, just do it like that::

   // You remove the given task from every application
   $workflow->removeTask(FlushCachesTask::class);

   // Only remove the task for a specific application
   $workflow->removeTask(FlushCachesTask::class, $application);

.. seealso:: How to create and add tasks
