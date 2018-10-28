# TYPO3 Surf - Powerful and flexible deployment tool for PHP projects [![Build Status](https://travis-ci.org/TYPO3/Surf.svg?branch=master)](https://travis-ci.org/TYPO3/Surf)

## Description

Surf package is a complete automated deployment tool. It is best used but by far not limited to deploy TYPO3 CMS and Flow applications. It's inspired by some
features of Capistrano (thanks) concerning the Git workflow.

Some of the features of the Surf package:

* Remote checkout of Git repositories with submodules (sorry, no SVN yet)
* Flexible, declarative configuration of deployments
* Multi-node, multi-application, multi-deployment deployments
* Hook in any deployment stage
* Create custom tasks with a few lines
* Simulate deployments with a dry run


## Installation

### Install Surf by downloading it from GitHub:


	mkdir /usr/local/surf
	curl -L https://github.com/TYPO3/Surf/releases/download/2.0.0-beta7/surf.phar -o /usr/local/surf/surf.phar
	chmod +x /usr/local/surf/surf.phar
	ln -s /usr/local/surf/surf.phar /usr/local/bin/surf


You may need extended privileges e.g. `sudo`.
In this way, you can add `/usr/local/surf` to `PHP Include Paths` of your IDE.


### Install Surf via composer


    # Until stable release, you need to set minimum-stability to beta
    composer global config minimum-stability beta
    composer global require typo3/surf:^2.0.0

In this way, you can add `~/.composer/vendor/typo3/surf` to `PHP Include Paths` of your IDE.

## Guide

### Deployment configurations

Each deployment is defined in a configuration (e.g for development, staging, live environments). Each *deployment configuration*
specifies a workflow for the deployment (for now there is just `SimpleWorkflow`, but feel free to create
your own). The deployment configuration has at least one application and one or more nodes for the application(s).

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

### SSH Authentication Types

The preferred way of connecting to the remote host is via SSH Public-Key authentication.
That's why in the example above, only the username and hostname are set.

However, due to constraints in the infrastructure setup, sometimes, deployment
scenarios do not work with public key authentication. Surf also supports
password-based SSH authentication. For that, you need to specify the password
as follows::

	$node->setOption('password', 'yourSshPasswordHere');

Authentication with passwords needs the ``expect`` unix tool which is installed
by default in most Linux distributions.

### Custom Connection

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

### Test a deployment

You can get a description of the deployment by running:

    $ surf describe MyDeployment

Simulate the deployment by running:

    $ surf simulate MyDeployment

The simulation gives a hint which tasks will be executed on which node. During simulation no harmful tasks will be
executed for real. If a remote SSH command would be executed it will be printed in the log messages starting with
``... $nodeName: "command"``.

### Flow version options

The Flow version used in a project can be set using:

	$application = new \TYPO3\Surf\Application\Neos\Flow();
	$application->setVersion('4.0');

It defaults to 4.0, so if using older Flow version, you need to set the version as `x.y`.
This switches Surf behavior to call Flow commands correctly.

### Flow Configuration overrides

If the configuration of a Flow application should be different depending on the deployment configuration
(e.g. database settings or external services) the TYPO3\\Surf\\Task\\Neos\\Flow\\CopyConfigurationTask task can be used to override
configuration after the code update (Git checkout).

If a ``Configuration`` folder exists inside a folder named after your deployment ``%FLOW_ROOT%/Build/Surf/MyDeployment``
every file in there will be copied to the release ``Configuration`` folder recursively.

### Run a deployment

If everything looks right, you can run the deployment:

    $ surf deploy MyDeployment

## Customization

### Using git for deployment

By default Surf use rsync and composer for deployment. But you can also use git, by adding the following configuration
to your Application::

	$application->setOption('transferMethod', 'git');
	$application->setOption('packageMethod', NULL);
	$application->setOption('updateMethod', NULL);

Using rsync can speed up your deployment and doesn't require composer and git on the production server.

### Custom tasks in deployment configurations

Since a deployment configuration is just a plain PHP file with access to any Flow class it's easy to extend it or program
a more complex behavior. But it's even easier to remove tasks or add some simple shell tasks to an existing application
template::

	<?php

	...
	$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();

	$workflow->defineTask('mycompany.mypackage:initialize',
		\TYPO3\Surf\Task\ShellTask::class,
		array('command' => 'cd {releasePath} && ./flow mycompany.mypackage:setup:initialize')
	);

This adds a new task based on the `TYPO3\\Surf\\Task\\ShellTask` task with a custom shell command which would run a Flow command.
After defining the new task we have to tell the deployment configuration when to execute it::

	<?php

	...
	$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();

	$application = new \TYPO3\Surf\Application\Neos\Flow('MyProject');

	$workflow->defineTask('mycompany.mypackage:initialize',
		\TYPO3\Surf\Task\ShellTask::class,
		array('command' => 'cd {releasePath} && ./flow mycompany.mypackage:setup:initialize')
	);

	$deployment->onInitialize(function() use ($workflow, $application) {
		$workflow->addTask('mycompany.mypackage:initialize', 'migrate', $application);
		$workflow->removeTask(\TYPO3\Surf\Task\Neos\Flow\SetFilePermissionsTask::class);
	});

This will execute the new task in the *migrate* stage only for the application referenced by ``$application``. As you can
see, it's also possible to remove a task from a workflow. Most of the methods are available for global or application
specific task configuration.

Besides specifying the execution point via a stage, you can also give an existing task as an anchor and specify the task
execution with `afterTask` or `beforeTask`.

### Task manipulation
<pre>
====================== ================================= ===================================================================================
Method                 Arguments                         Description
====================== ================================= ===================================================================================
defineTask             $taskName, $taskType, ($options)  Defines a new task with name $taskName based on $taskType with custom options.
addTask                $tasks, $stage, ($application)    Add one or more tasks to the workflow that should run in the given stage.
removeTask             $taskName                         Removes the task with the given name from all stages and applications.
afterTask              $taskName, $tasks, ($application) Adds one or more tasks that should run *after* the given task name.
beforeTask             $taskName, $tasks, ($application) Adds one or more tasks that should run *before* the given task name.
====================== ================================= ===================================================================================
</pre>

### Shell Task option expansion

To access the release path or other release specific options, some placeholders can be used in option values::

	<?php

	...
	$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();

	$workflow->defineTask('mycompany.mypackage:initialize',
		\TYPO3\Surf\Task\ShellTask::class,
		array('command' => 'cd {releasePath} && ./flow mycompany.mypackage:setup:initialize')
	);

The following placeholders are available:

* workspacePath: The path to the local workspace directory
* deploymentPath: The path to the deployment base directory
* releasePath: The path to the release directory in work (typically referenced by *next*)
* sharedPath: The path to the shared directory for all releases
* currentPath: The path that points to the *current* release
* previousPath: The path that points to the *previous* release

## Smoke Testing

As you do automated deployments, you should check if the website is up and running
before switching it to the live site. This is called a *Smoke Test*. We will give
an example for using the built-in HTTP smoke test.

First, you need to create a virtual host with document root in "<deploymentDirectory>/releases/next/Web".
While a deployment is running, the new website will be available under this URL and can
be used for testing.

Then, add a test as follows to the deployment configuration::

	$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();

	$smokeTestOptions = array(
		'url' => 'http://your/website/which/you/want/to/test',
		'remote' => TRUE,
		'expectedStatus' => 200,
		'expectedRegexp' => '/somethingYouExpectOnThePage/'
	);
	$workflow->defineTask('mycompany.mypackage:smoketest', \TYPO3\Surf\Task\Test\HttpTestTask::class, $smokeTestOptions);

	$workflow->addTask('mycompany.mypackage:smoketest', 'test', $application);

The HTTP test has the following options:

Most important options:

* url (required): URL which should be loaded
* remote: if TRUE, the smoke test is triggered through the SSH channel on the remote host
  via command-line CURL. If false, it is triggered from the deploying host.
* expectedStatus: expected HTTP status code
* expectedHeaders: HTTP Header Strings which are expected (can be a multiline string,
  each header being on a separate line)
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

## Applying Cherry-Picks to Git Repositories: Post-Checkout commands

When you want to execute some commands directly after checkout, such as cherry-picking
not-yet-committed bugfixes, you can set the  `gitPostCheckoutCommands` option on the
application, being a two-dimensional array.

The key contains the path where the command shall execute, and the value is another
array containing the commands themselves.

Example::

	$application->setOption('gitPostCheckoutCommands', array(
		'Packages/Framework/Neos.Flow/' => [
			'git fetch https://github.com/neos/flow-development-collection.git refs/heads/somefix',
			'git cherry-pick FETCH_HEAD'
        ]
	));

## Environment Variables

* SURF_WORKSPACE, defines where the Workspace of Surf is saved (Default: ~/.surf/workspace)

## Common Problems

### Some providers may have SSH rate limits

Source and details: https://karsten.dambekalns.de/blog/using-ssh-controlmaster-with-typo3-surf.html

> SSH provides a way to reuse an existing SSH connection for subsequent connection attempts to the same host.

Add something like this to `~/.ssh/config` to reuse existing SSH connections:

```
Host myhost.uberspace.de
ControlMaster auto
ControlPath /tmp/ssh_mux_%h_%p_%r
ControlPersist 600
```

## Building a Surf PHAR from source

Surf is built using the box project (https://box-project.github.io/box2/) and the
process is simple:

* Download the latest `box.phar` and put in an executable directory
* `cd your/surf/clone`
* `php box build`

The generated `surf.phar` should work as expected.

## Contributing to the documentation

You can simply edit or add a .rst file in the `Documentation` folder on Github and create a pull request.

The online documentation will automatically update after changes to the master branch.
To preview the documentation locally please follow this [guide](https://github.com/t3docs/docker-render-documentation).

The documentation was set up according to the [TYPO3 documentation guide](https://docs.typo3.org/typo3cms/RenderTYPO3DocumentationGuide/Index.html).

## Copyright

The deployment package is licensed under GNU General Public License, version 3 or later
(http://www.gnu.org/licenses/gpl.html). Initial development was sponsored by
[networkteam - Flow Framework Agentur](https://networkteam.com/fokus/flow-framework.html).
