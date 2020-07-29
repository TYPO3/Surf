.. -*- coding: utf-8 -*- with BOM.
.. include:: ../Includes.txt

=====
Tasks
=====

Since a deployment configuration is just a plain PHP file you can create custom tasks by creating for example a **NpmInstallTask** class which itself must extend in the whole inheritance chain the abstract class **\\TYPO3\\Surf\\Domain\\Model\\Task** shipped with Surf::

   <?php

   namespace Vendor\MyNamespace;

   use TYPO3\Surf\Domain\Model\Application;
   use TYPO3\Surf\Domain\Model\Deployment;
   use TYPO3\Surf\Domain\Model\Node;
   use TYPO3\Surf\Task\LocalShellTask

   class NpmInstallTask extends LocalShellTask
   {
       /**
        * Executes this action
        *
        * @param \TYPO3\Surf\Domain\Model\Node $node
        * @param \TYPO3\Surf\Domain\Model\Application $application
        * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
        * @param array $options
        */
       public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
       {
           if (!isset($options['command'])) {
               $options['command'] = 'cd {workspacePath} && npm install';
           }

           parent::execute($node, $application, $deployment, $options);
       }
   }


In this case we create a task to install the npm dependencies locally.
The example shows some simple things to be aware of.
First of all you see the string **{workspacePath}**. This placeholder gets replaced by the full workspace path for the current application.

We will see later that for the remote shell tasks, we have more of these placeholders (:ref:`tasks-placeholders`).
Secondly we see that the task defines a default command if none is specified. If you like you can override this option in your deployment configuration.
This is valid for all the delivered tasks shipped with Surf.

For this simple task above, we recommend to simplify this by just using the possibility to define your **task dynamically** by the following mechanism provided by Surf::

    <?php

    ...

    $workflow->defineTask('NpmInstallTask', \TYPO3\Surf\Task\LocalShellTask::class, [
        'command' => [
            'cd {workspacePath} && npm install',
        ]
    ]);

This way you create a task dynamically by extending the base task \\TYPO3\\Surf\\Task\\LocalShellTask::class with an array of options. In this case the command option is mandatory for the LocalShellTask.

We will show you another convenient and often used way to customize the deployment workflow with your own tasks::

    <?php

    ...

    $workflow->defineTask('CopyEnvFileTask', \TYPO3\Surf\Task\ShellTask::class, [
        'command' => [
            "cp {sharedPath}/.env {releasePath}/.env",
            "cd {releasePath}",
        ]
    ]);

In this case we create a task dynamically based on the ShellTask. The ShellTask execute one or more provided commands on the target machine.
As you can see, we have used some other placeholders compared to the LocalShellTask above.
For the remote ShellTask the following placeholders are available:

.. _tasks-placeholders:

Placeholders
------------

* **workspacePath**: The path to the local workspace directory
* **deploymentPath**: The path to the deployment base directory
* **releasePath**: The path to the release directory in work (typically referenced by *next*)
* **sharedPath**: The path to the shared directory for all releases
* **currentPath**: The path that points to the *current* release
* **previousPath**: The path that points to the *previous* release

Add task to the deployment flow
-------------------------------

So we have seen how to create custom tasks in different ways. In the following we will see how we add these tasks to the deployment flow::

    <?php
    /** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

    //...

    $application = new \TYPO3\Surf\Application\TYPO3\CMS();

    $deployment->onInitialize(function () use ($deployment, $application) {
        $deployment->getWorkflow()
            ->defineTask('CopyEnvFileTask', \TYPO3\Surf\Task\ShellTask::class, [
                'command' => [
                    "cp {sharedPath}/.env {releasePath}/.env",
                    "cd {releasePath}",
                ]
            ])
            ->afterStage('transfer', 'CopyEnvFileTask', $application);
    });

This will execute the new task after the stage transfer only for the application referenced by $application.

Besides specifying the execution point via a stage, you can also give an existing task as an anchor and specify the task execution with **afterTask** or **beforeTask**::

    <?php
    /** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

    //...
    $deployment->onInitialize(function () use ($deployment, $application) {
        $deployment->getWorkflow()
            ->beforeTask(SomeTask::class, [
                'CopyEnvFileTask'
            ]);
    });


The following table shows all the methods to manipulate the tasks in the deployment flow (part of the abstract Workflow class):

====================== ================================= ===================================================================================
Method                 Arguments                         Description
====================== ================================= ===================================================================================
defineTask             $taskName, $taskType, ($options)  Defines a new task with name $taskName based on $taskType with custom options.
addTask                $tasks, $stage, ($application)    Add one or more tasks to the workflow that should run in the given stage.
removeTask             $taskName                         Removes the task with the given name from all stages and applications.
afterTask              $taskName, $tasks, ($application) Adds one or more tasks that should run *after* the given task name.
beforeTask             $taskName, $tasks, ($application) Adds one or more tasks that should run *before* the given task name.
====================== ================================= ===================================================================================

Options for Task
----------------

In order to customize options of existing tasks you can do it the following ways::

    <?php

    use TYPO3\Surf\Task\Transfer\RsyncTask;

    ...

    // Customize the option for the task only for a specific application
    $application->setOption(RsyncTask::class . '[rsyncExcludes]', [
        '.git',
        'web/fileadmin',
        'web/uploads',
    ]);

    // Customize the option for the task only for a specific node
    $node->setOption(RsyncTask::class . '[rsyncExcludes]', [
        'web/fileadmin',
        'web/uploads',
    ]);

    // Customize the option for the whole deployment
    $deployment->setOption(RsyncTask::class . '[rsyncExcludes]', [
        '.git',
    ]);


The **order is important** because application options override node options and node options override deployment options.
