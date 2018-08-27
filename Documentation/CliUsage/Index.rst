.. -*- coding: utf-8 -*- with BOM.
.. include:: ../Includes.txt

=========
CLI Usage
=========

After installation of Surf you will have the ability to run the surf command from your terminal.

Surf will by default check for your deployment configurations in the subfolder `.surf`.

To get list of all available tasks run the ``surf`` command::

    TYPO3 Surf [version]

    Usage:
      command [options] [arguments]

    Options:
      -h, --help            Display this help message
      -q, --quiet           Do not output any message
      -V, --version         Display this application version
          --ansi            Force ANSI output
          --no-ansi         Disable ANSI output
      -n, --no-interaction  Do not ask any interactive question
      -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

    Available commands:
      deploy    Deploys the given name
      describe  Describes the flow for the given name
      help      Displays help for a command
      list      Lists commands
      migrate   Migrates old deployment definitions to new Surf version
      show      Shows all the deployments depending on the directory configuration
      simulate  Simulates the deployment for the given name


List available deployments
--------------------------

You can get a list of available deployments by running::

    $ surf show

Test a deployment
-----------------

You can get a description of the deployment by running::

    $ surf describe MyDeployment

Simulate the deployment by running::

    $ surf simulate MyDeployment

The simulation gives a hint which tasks will be executed on which node. During simulation no harmful tasks will be
executed for real. If a remote SSH command would be executed it will be printed in the log messages starting with
``... $nodeName: "command"``.

Run a deployment
----------------

If everything looks right, you can run the deployment::

    $ surf deploy MyDeployment

.. _cli-usage-configuration-path-section:

Using a different configuration path
------------------------------------

If you want to use a different configuration path than `.surf` use the provided option like this::

    $ surf show --configurationPath myConfigurationPath

