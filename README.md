TYPO3.Surf - FLOW3 deployment
====================================

## DESCRIPTION

The deploy package is a complete automatic deployment tool powered by FLOW3. It's best used bot not limited to deploy FLOW3 applications. It might be included in your FLOW3 application but can be run standalone. It's inspired by some features of Capistrano (thanks) concerning the Git workflow.

Some of the features of the deploy package:

* Remote checkout of Git repositories with submodules (sorry, no SVN yet)
* Flexible, declarative configuration of deployments
* Multi-node, multi-application, multi-deployment deployments
* Hook in any deployment stage
* Create custom tasks with a few lines
* Simulate deployments with a dry run

Note that the final name of the package and options / API are very likely to change.

## INSTALLATION

Install the deploy package by cloning this Github repository to `FLOW3_ROOT/Packages/Application/TYPO3.Surf` of a FLOW3 installation.

## GUIDE

Eeach deployment is a distinct configuration (e.g for development, staging, live environments). Eeach deployment configuration specifies a workflow for the deployment (for now it's just _SimpleWorkflow_, but feel free to create your own), at least one application and assigns nodes to the application(s).

We start by creating a simple deployment configuration in `FLOW3_ROOT/Build/Deploy/MyDeployment.php` for a deployment with name _MyDeployment_:

```php
<?php
  $workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();
  $deployment->setWorkflow($workflow);

  $node = new \TYPO3\Surf\Domain\Model\Node('example');
  $node->setHostname('example.com');
  $node->setOption('username', 'myuser');
  $deployment->addNode($node);

  $application = new \TYPO3\Surf\Application\FLOW3();
  $application->setDeploymentPath('/home/my-flow3-app/app');
  $application->setOption('repositoryUrl', 'git@github.com:myuser/my-flow3-app.git');
  $application->addNode($node);
  $deployment->addApplication($application);
?>
```

That's a very basic deployment based on the FLOW3 application template `TYPO3\Surf\Application\FLOW3`. The deployment object is available to the script as the variable `$deployment`. A node is basically a deployment target representing a host. The Node is assigned to the application for the deployment. Finally the application is added to the deployment.

You can get a description of the deployment by running:

    $ ./flow3 deploy:describe MyDeployment

Simulate the deployment by running:

    $ ./flow3 deploy:simulate MyDeployment

If everything looks right, you can run it:

    $ ./flow3 deploy:deploy MyDeployment

## CUSTOMIZATION

Since a deployment descriptor is just a plain PHP file with access to any FLOW3 class it's easy to extend it or program a more complex behavior. But it's even easier to remove tasks or add some simple shell tasks to an existing application template:

```php
<?php
  ...

  $workflow->defineTask('mycompany.mypackage:initialize',
  	'typo3.deploy:shell',
  	array('command' => 'cd {releasePath} && ./flow3 mycompany.mypackage:setup:initialize')
  );
?>
```

This adds a new task based on the `typo3.deploy:shell` task with a custom shell command which would run a FLOW3 command. After defining the new task we have to tell the deployment configuration when to execute it:

```php
<?php
  ...

  $workflow->defineTask('mycompany.mypackage:initialize',
  	'typo3.deploy:shell',
  	array('command' => 'cd {releasePath} && ./flow3 mycompany.mypackage:setup:initialize')
  );

  $deployment->onInitialize(function() use ($workflow, $application) {
  	$workflow->forApplication($application, 'migrate', 'mycompany.mypackage:initialize');
  	$workflow->removeTask('typo3.deploy:flow3:setfilepermissions');
  });

?>
```

This will execute the new task in the _migrate_ stage only for the application referenced by `$application`. As you can see, it's also possible to remove a task from a workflow. Most of the methods are available for global or application specific task configuration.

Besides specifying the execution point via a stage, you can also give an existing task as an anchor and specify the task execution with `afterTask` or `beforeTask`.

## COPYRIGHT

The deployment package is licensed under GNU General Public License, version 3 or later (http://www.gnu.org/licenses/gpl.html). Initial development was sponsored by [networkteam - FLOW3 Agentur](http://www.networkteam.com/flow3-agentur.html).
