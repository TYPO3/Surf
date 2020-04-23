***************
Option Handling
***************

During deployment configuration, options can be set in several places.

These options are used for the execution of the tasks according to priority, depending on the context set.

Priority ascending:
 * Deployment
 * Node
 * Application
 * Task

With the same key, options in higher priority will overwrite those with lower priority.
The options are resolved at runtime ``$this->configureOptions();``

Deployment Options
==================
can be set as follows::

    $deployment->setOption($optionName, $optionValue);

Node Options
============
can be set as follows::

    $node->setOption($optionName, $optionValue);

Application Options
===================
can be set as follows::

    // multiple options as array with
    $application->setOptions($options);
    // single option with
    $application->setOption($optionName, $value);

Task Options
============
can be set as follows::

    // while defining a new task with
    $workflow->defineTask($taskName, $options)
    // or merge(overwrite) existing task-options with
    $workflow->setTaskOptions($taskName, $options);

Option Name
===========
Depending on the use case of the option you have to choose the option name:

Options for specific Tasks:
these would be combinations of the **fully qualified class name** and **option name** in sqare brackets::

    $optionName = 'fullQualifiedClassName[optionName]'

The **fully qualified class name** can be defined as follows::

    'TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask'
    // or by using php class-constant
    \TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask::class
