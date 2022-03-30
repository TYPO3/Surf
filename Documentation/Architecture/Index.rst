.. include:: /Includes.rst.txt

============
Architecture
============

In order to better understand the concept of the Surf deployment process we will explain some fundamental terminology in this section.

Basically you should grasp the following four main terms to understand the basic concept of the deployment process:

Workflow
--------
The workflow defines the execution process of the deployment and consists of a number of stages and tasks for every stage.
Surf ships already with one concrete Workflow called **SimpleWorkflow**.
But you can define your own workflow as long as your workflow class inherits from the `\TYPO3\Surf\Domain\Model\Workflow` class shipped with Surf.

You assign the workflow to the deployment class.

.. seealso:: :doc:`Detailed description of the deployment flow <../DeploymentFlow/Index>`.

Application
-----------
The application is the code you want to ship. The deployment configuration has at least one application.
But you can configure as many applications as you want in one deployment configuration.
The application itself contains of one or more nodes.
You can also define within the workflow whether a task is to apply to all applications or only to a specific application.

.. seealso:: :doc:`Applications <../Applications/Index>`.

Node
----
A node is basically a deployment target representing a server for an application.
The **node** is assigned to an **application** for the deployment.

.. seealso:: :doc:`How to configure a node <../Nodes/Index>`.

Task
----

A task is the smallest unit in the whole deployment process.
A task consists of commands either executed locally or remotely.

A task can be applied for all applications or only for certain applications.

.. seealso:: :doc:`Detailed configuration of tasks <../Tasks/Index>`.
