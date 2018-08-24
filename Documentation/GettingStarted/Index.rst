.. -*- coding: utf-8 -*- with BOM.
.. include:: ../Includes.txt

===============
Getting Started
===============

First of all you have to :doc:`install Surf <../Installation/Index>`.

After installing Surf you have to create one or more deployment configuration files for your project.
The deployment configuration files are at the moment just plain php files.
So you can do what ever you can dream of what is possible with php itself.
We recommend to keep the deployment configuration as simple as possible and do it in the first place in a procedural like style.

Per default Surf expects the deployment configuration files within the .surf directory in your project.

If you like you can specify the configuration directory with the command option --configurationPath. (:ref:`cli-usage-configuration-path-section`)

But for now we are going to place our deployment configuration files in the .surf directory.

We start by creating a simple deployment configuration in `~/.surf/MyDeployment.php` for a deployment
with name **MyDeployment**::

   <?php
   $node = new \TYPO3\Surf\Domain\Model\Node('example');
   $node->setHostname('example.com');
   $node->setOption('username', 'myuser');

   $application = new \TYPO3\Surf\Application\Neos\Flow();
   $application->setVersion('4.0');
   $application->setDeploymentPath('/home/my-flow-app/app');
   $application->setOption('repositoryUrl', 'git@github.com:myuser/my-flow-app.git');
   $application->addNode($node);

   $deployment->addApplication($application);

That's a very basic deployment based on the default Flow application template ``TYPO3\Surf\Application\Neos\Flow``.
The deployment object is available to the script as the variable ``$deployment``. A *node* is basically a deployment
target representing a server for an application. The node is assigned to the applications for the deployment. Finally
the application is added to the deployment.

Each application resembles a repository with code. So a more complex deployment could both deploy a Flow application
and release an extension for a TYPO3 CMS website. Also different roles can be expressed using applications, since every
task can be registered to run for all or a specific application instance.

In this basic deployment above are a lot of sensitive defaults configured behind the curtains.
You are going to explore them in the different chapters of the documentation.

SSH Authentication Types
========================

The preferred way of connecting to the remote host is via SSH Public-Key authentication.
That's why in the example above, only the username and hostname are set.

However, due to constraints in the infrastructure setup, sometimes, deployment
scenarios do not work with public key authentication. Surf also supports
password-based SSH authentication. For that, you need to specify the password
as follows::

$node->setOption('password', 'yourSshPasswordHere');

Authentication with passwords needs the ``expect`` unix tool which is installed
by default in most Linux distributions.

Custom Connection
=================

In case you need to connect to the remote host via more esoteric protocols, you can
also implement your own remote host connection: In this case, set the option
``remoteCommandExecutionHandler`` on the node::

   $node->setOption('remoteCommandExecutionHandler', function(\TYPO3\Surf\Domain\Service\ShellCommandService $shellCommandService, $command, Node $node, Deployment $deployment, $logOutput = TRUE) {
      // Now, do what you need to do in order to connect to $node and execute $command.
      // You can call $shellCommandService->executeProcess() here.

      // This function should return a two-element array where the first array element
      // is an integer containing the Exit Code, and the second array element is a
      // string with the full, trimmed, output.
   });

Test a deployment
=================

You can get a description of the deployment by running::

    $ surf describe MyDeployment

Simulate the deployment by running::

    $ surf simulate MyDeployment

The simulation gives a hint which tasks will be executed on which node. During simulation no harmful tasks will be
executed for real. If a remote SSH command would be executed it will be printed in the log messages starting with
``... $nodeName: "command"``.

But be aware, if the simulation is working fine it does not mean everything is working correctly for the real deployment.
Sorry for that. We are working on it to test really everything during the simulation. But this task is difficult, we tell you.

Run a deployment
================

If everything looks right, you can run the deployment::

    $ surf deploy MyDeployment

To include extra details in the output, you can increase verbosity with the --verbose option:

   - -v for normal output,
   - -vv for more verbose output,
   - -vvv for debug.

Surf is going to create the following directories on the deployment host:

   - releases contains releases dirs,
   - shared contains shared files and dirs,
   - releases/next (temporarily)
   - releases/current symlink to current release
   - releases/previous (optional)

Configure your hosts to serve your public directory from current.

By default Surf keeps the all the releases, but you can configure this number by modifying the associated option::

   $application->setOption('keepReleases', 2);

Customization
=============

Using git for deployment
------------------------

By default Surf uses rsync and composer for deployment. But you can also use git, by adding the following configuration
to your Application::

   $application->setOption('transferMethod', 'git');
   $application->setOption('packageMethod', NULL);
   $application->setOption('updateMethod', NULL);

Using rsync can speed up your deployment and doesn't require composer and git on the production server.
