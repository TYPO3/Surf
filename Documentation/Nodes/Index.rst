.. -*- coding: utf-8 -*- with BOM.
.. include:: ../Includes.txt

=====
Nodes
=====

A node is basically a deployment target representing a server for an application. The node is assigned to an application for the deployment.

A simple node configuration looks like this::

   ...
   $node = new \TYPO3\Surf\Domain\Model\Node('example');
   $node->setHostname('example.com');
   $node->setOption('username', 'myuser');

   $application->addNode($node);


SSH Authentication Types
------------------------
The preferred way of connecting to the remote host is via SSH Public-Key authentication. That's why in the example above, only the username and hostname are set.

However, due to constraints in the infrastructure setup, sometimes, deployment scenarios do not work with public key authentication. Surf also supports password-based SSH authentication. For that, you need to specify the password as follows::

   $node->setOption('password', 'yourSshPasswordHere');

Custom Connection
-----------------

In case you need to connect to the remote host via more esoteric protocols, you can also implement your own remote host connection: In this case, set the option remoteCommandExecutionHandler on the node::

   $node->setOption('remoteCommandExecutionHandler', function(ShellCommandService $shellCommandService, $command, Node $node, Deployment $deployment, $logOutput = TRUE) {
      // Now, do what you need to do in order to connect to $node and execute $command.
      // You can call $shellCommandService->executeProcess() here.

      // This function should return a two-element array where the first array element
      // is an integer containing the Exit Code, and the second array element is a
      // string with the full, trimmed, output.
   });


Some providers may have SSH rate limits
---------------------------------------

Source and details: https://karsten.dambekalns.de/blog/using-ssh-controlmaster-with-typo3-surf.html

.. note:: SSH provides a way to reuse an existing SSH connection for subsequent connection attempts to the same host.

Add something like this to ~/.ssh/config to reuse existing SSH connections::

   Host myhost.uberspace.de
   ControlMaster auto
   ControlPath /tmp/ssh_mux_%h_%p_%r
   ControlPersist 600
