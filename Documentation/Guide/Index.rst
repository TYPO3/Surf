====================================
Surf - TYPO3 Flow deployment
====================================

Description
===========

The Surf package is a complete automated deployment tool powered by TYPO3 Flow. It's best used but not limited to deploy
Flow applications. It might be included in your Flow application but can also be run standalone. It's inspired by some
features of Capistrano (thanks) concerning the Git workflow.

Some of the features of the Surf package:

* Remote checkout of Git repositories with submodules (sorry, no SVN yet)
* Flexible, declarative configuration of deployments
* Multi-node, multi-application, multi-deployment deployments
* Hook in any deployment stage
* Create custom tasks with a few lines
* Simulate deployments with a dry run

.. note:: Surf ist still work-in-progress. API and options are subject to change.

Installation
============

Install the Surf package by importing the package to a TYPO3 Flow application:

.. code-block:: none

	./flow package:import TYPO3.Surf

Guide
=====

Deployment configurations
-------------------------

Each deployment is defined in a configuration (e.g for development, staging, live environments). Each *deployment configuration*
specifies a workflow for the deployment (for now there is just ``SimpleWorkflow``, but feel free to create
your own). The deployment configuration has at least one application and one or more nodes for the application(s).

We start by creating a simple deployment configuration in ``%FLOW_ROOT%/Build/Surf/MyDeployment.php`` for a deployment
with name **MyDeployment**::

	<?php
	$node = new \TYPO3\Surf\Domain\Model\Node('example');
	$node->setHostname('example.com');
	$node->setOption('username', 'myuser');

	$application = new \TYPO3\Surf\Application\TYPO3\Flow();
	$application->setDeploymentPath('/home/my-flow-app/app');
	$application->setOption('repositoryUrl', 'git@github.com:myuser/my-flow-app.git');
	$application->addNode($node);

	$deployment->addApplication($application);
	?>

That's a very basic deployment based on the default Flow application template ``TYPO3\Surf\Application\TYPO3\Flow``.
The deployment object is available to the script as the variable ``$deployment``. A *node* is basically a deployment
target representing a server for an application. The node is assigned to the applications for the deployment. Finally
the application is added to the deployment.

Each application resembles a repository with code. So a more complex deployment could both deploy a Flow application
and release an extension for a TYPO3 CMS website. Also different roles can be expressed using applications, since every
task can be registered to run for all or a specific application instance.

SSH Authentication Types
------------------------

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
-----------------

In case you need to connect to the remote host via more esoteric protocols, you can
also implement your own remote host connection: In this case, set the option
``remoteCommandExutionHandler`` on the node::

	<?php
	$node->setOption('remoteCommandExutionHandler', function(\TYPO3\Surf\Domain\Service\ShellCommandService $shellCommandService, $command, Node $node, Deployment $deployment, $logOutput = TRUE) {
		// Now, do what you need to do in order to connect to $node and execute $command.
		// You can call $shellCommandService->executeProcess() here.

		// This function should return a two-element array where the first array element
		// is an integer containing the Exit Code, and the second array element is a
		// string with the full, trimmed, output.
	});
	?>

Test a deployment
-----------------

You can get a description of the deployment by running:

.. code-block:: none

    $ ./flow surf:describe MyDeployment

Simulate the deployment by running:

.. code-block:: none

    $ ./flow surf:simulate MyDeployment

The simulation gives a hint which tasks will be executed on which node. During simulation no harmful tasks will be
executed for real. If a remote SSH command would be executed it will be printed in the log messages starting with
``... $nodeName: "command"``.

Flow Configuration overrides
-----------------------

If the configuration of a Flow application should be different depending on the deployment configuration
(e.g. database settings or external services) the typo3.surf:typo3:flow:copyconfiguration task can be used to override
configuration after the code update (Git checkout).

If a ``Configuration`` folder exists inside a folder named after your deployment ``%FLOW_ROOT%/Build/Surf/MyDeployment``
every file in there will be copied to the release ``Configuration`` folder recursively.

Run a deployment
----------------

If everything looks right, you can run the deployment:

.. code-block:: none

    $ ./flow surf:deploy MyDeployment

Customization
=============

Custom tasks in deployment configurations
-----------------------------------------

Since a deployment configuration is just a plain PHP file with access to any Flow class it's easy to extend it or program
a more complex behavior. But it's even easier to remove tasks or add some simple shell tasks to an existing application
template::

	<?php

	...
	$workflow = $deployment->getWorkflow();

	$workflow->defineTask('mycompany.mypackage:initialize',
		'typo3.surf:shell',
		array('command' => 'cd {releasePath} && ./flow mycompany.mypackage:setup:initialize')
	);

	?>


This adds a new task based on the `typo3.surf:shell` task with a custom shell command which would run a Flow command.
After defining the new task we have to tell the deployment configuration when to execute it::

	<?php

	...
	$workflow = $deployment->getWorkflow();

	$application = new \TYPO3\Surf\Application\TYPO3\Flow('MyProject');

	$workflow->defineTask('mycompany.mypackage:initialize',
		'typo3.surf:shell',
		array('command' => 'cd {releasePath} && ./flow mycompany.mypackage:setup:initialize')
	);

	$deployment->onInitialize(function() use ($workflow, $application) {
		$workflow->addTask('mycompany.mypackage:initialize', 'migrate', $application);
		$workflow->removeTask('typo3.surf:typo3:flow:setfilepermissions');
	});

	?>


This will execute the new task in the *migrate* stage only for the application referenced by ``$application`. As you can
see, it's also possible to remove a task from a workflow. Most of the methods are available for global or application
specific task configuration.

Besides specifying the execution point via a stage, you can also give an existing task as an anchor and specify the task
execution with `afterTask` or `beforeTask`.

Task manipulation
-----------------

====================== ================================= ===================================================================================
Method                 Arguments                         Description
====================== ================================= ===================================================================================
defineTask             $taskName, $taskType, ($options)  Defines a new task with name $taskName based on $taskType with custom options.
addTask                $tasks, $stage, ($application)    Add one or more tasks to the workflow that should run in the given stage.
removeTask             $taskName                         Removes the task with the given name from all stages and applications.
afterTask              $taskName, $tasks, ($application) Adds one or more tasks that should run *after* the given task name.
beforeTask             $taskName, $tasks, ($application) Adds one or more tasks that should run *before* the given task name.
====================== ================================= ===================================================================================

Shell Task option expansion
---------------------------

To access the release path or other release specific options, some placeholders can be used in option values::

	<?php

	...
	$workflow = $deployment->getWorkflow();

	$workflow->defineTask('mycompany.mypackage:initialize',
		'typo3.surf:shell',
		array('command' => 'cd {releasePath} && ./flow mycompany.mypackage:setup:initialize')
	);

	?>

The following placeholders are available:

* deploymentPath: The path to the deployment base directory
* releasePath: The path to the release directory in work (typically referenced by *next*)
* sharedPath: The path to the shared directory for all releases
* currentPath: The path that points to the *current* release
* previousPath: The path that points to the *previous* release

Smoke Testing
=============

As you do automated deployments, you should check if the website is up and running
before switching it to the live site. This is called a *Smoke Test*. We will give
an example for using the built-in HTTP smoke test.

First, you need to create a virtual host with document root in "<deploymentDirectory>/releases/next/Web".
While a deployment is running, the new website will be available under this URL and can
be used for testing.

Then, add a test as follows to the deployment configuration::


	$workflow = $deployment->getWorkflow();

	$smokeTestOptions = array(
		'url' => 'http://your/website/which/you/want/to/test',
		'remote' => TRUE,
		'expectedStatus' => 200,
		'expectedRegexp' => '/somethingYouExpectOnThePage/'
	);
	$workflow->defineTask('yourNamespace:smoketest', 'typo3.surf:test:httptest', $smokeTestOptions);

	$workflow->addTask('yourNamespace:smoketest', 'test', $application);

The HTTP test has the following options:

Most important options:

* url (required): URL which should be loaded
* remote: if TRUE, the smoke test is triggered through the SSH channel on the remote host
  via command-line CURL. If false, it is triggered from the deploying host.
* expectedStatus: expected HTTP status code
* expectedHeaders: HTTP Header Strings which are expected (can be a multiline string, each header being on
  a separate line)
* expectedRegexp: Regular Expression to test the contents of the HTTP response against

Further options:

* timeout (only if remote=FALSE): HTTP timeout to use
* port (only if remote=FALSE): HTTP Port to use
* method (only if remote=FALSE): HTTP method to use (default GET)
* username (only if remote=FALSE): HTTP Authentication username
* password (only if remote=FALSE): HTTP Authentication Password
* data (only if remote=FALSE): HTTP payload
* proxy (only if remote=FALSE): HTTP Proxy to use
* proxyPort  (only if remote=FALSE): HTTP Proxy port to use
* additionalCurlParameters (only if remote=TRUE): list of parameters which
  is directly passed to CURL. Especially useful to e.g. disable SSL certificate
  check (with --insecure)

Applying Cherry-Picks to Git Repositories: Post-Checkout commands
=================================================================

When you want to execute some commands directly after checkout, such as cherry-picking not-yet-committed bugfixes, you can set the  `gitPostCheckoutCommands` option on the application, being a two-dimensional array.
The key contains the path where the command shall execute, and the value is another array containing the commands themselves (as taken f.e. from Gerrit / review.typo3.org).
Example::

	$application->setOption('gitPostCheckoutCommands', array(
		'Packages/Framework/TYPO3.Flow/' => array('git fetch git://git.typo3.org/Flow/Packages/TYPO3.Flow refs/changes/59/6859/1 && git cherry-pick FETCH_HEAD')
	));

Copyright
=========

The deployment package is licensed under GNU General Public License, version 3 or later (http://www.gnu.org/licenses/gpl.html). Initial development was sponsored by [networkteam - TYPO3 Flow Agentur](http://www.networkteam.com/typo3-flow-agentur.html).
