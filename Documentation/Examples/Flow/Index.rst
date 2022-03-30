.. include:: /Includes.rst.txt
.. index:: Flow

=======================
How to deploy Flow Apps
=======================

Flow version options
--------------------

The Flow version used in a project can be set using::

   <?php
   /** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

   $application = new \TYPO3\Surf\Application\Neos\Flow();
   $application->setVersion('4.0');

It defaults to 4.0, so if you are using an older Flow version, you need to set the version as `x.y`.
This switches Surf behavior to call Flow commands correctly.

.. seealso:: :doc:`See the Neos example for more details <../Neos/Index>`.

Flow Configuration overrides
----------------------------

If the configuration of a Flow application should be different depending on the deployment configuration
(e.g. database settings or external services) the `TYPO3\Surf\Task\Neos\Flow\CopyConfigurationTask` task can be used to override
configuration after the code update (Git checkout).

If a ``Configuration`` folder exists inside a folder named after your deployment ``~/.surf/deployments/MyDeployment``
every file in there will be copied to the release ``Configuration`` folder recursively.
