.. -*- coding: utf-8 -*- with BOM.
.. include:: ../Includes.txt

============
Installation
============

There are several ways to install Surf:

1. :ref:`installation-download-phar`
2. :ref:`installation-global-composer`
3. :ref:`installation-local-composer`
4. :ref:`installation-build-phar`

.. _installation-download-phar:

Download phar archive
---------------------

To install Surf as phar archive, run the following commands::

    mkdir /usr/local/surf
    curl -L https://github.com/TYPO3/Surf/releases/download/2.1.1/surf.phar -o /usr/local/surf/surf.phar
    chmod +x /usr/local/surf/surf.phar
    ln -s /usr/local/surf/surf.phar /usr/local/bin/surf

You may need extended privileges e.g. `sudo`.

This way, you can add `/usr/local/surf` to `PHP Include Paths` in your IDE.

Upgrading Surf
~~~~~~~~~~~~~~

Later, to upgrade Surf, run the command::

    surf self-update

.. _installation-global-composer:

Global composer installation
----------------------------

To install Surf globally via composer, run the following command::

    composer global require typo3/surf:^2.0

This way, you can add `~/.composer/vendor/typo3/surf` to `PHP Include Paths` in your IDE.

.. _installation-local-composer:

Local composer installation
----------------------------

To install Surf within your project via composer, run the following command::

    composer require typo3/surf:^2.0

The advantage of this method is that you can directly inspect the source files of surf without any further configuration in your IDE.

.. _installation-build-phar:

Building a Surf phar from source
--------------------------------

Surf is built using `humbug/box <https://github.com/humbug/box/>`_ and the process is simple:

* Install humbug/box as described in its documentation
* Clone or download the desired branch of typo3/surf
* `cd your/surf/folder`
* `composer install --no-dev`
* `path/to/box compile`

The generated `surf.phar` in the folder `release` should work as expected.
