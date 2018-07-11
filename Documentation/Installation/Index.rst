.. -*- coding: utf-8 -*- with BOM.
.. include:: ../Includes.txt
============
Installation
============

There are two ways to install Surf:

1. download phar archive
2. source composer installation

---------------------
Download phar archive
---------------------

To install Surf as phar archive, run the following commands::

    mkdir /usr/local/surf
    curl -L https://github.com/TYPO3/Surf/releases/download/2.0.0-beta7/surf.phar -o /usr/local/surf/surf.phar
    chmod +x /usr/local/surf/surf.phar
    ln -s /usr/local/surf/surf.phar /usr/local/bin/surf

You may need extended privileges e.g. `sudo`.

This way, you can add `/usr/local/surf` to `PHP Include Paths` of your IDE.

Upgrading Surf
--------------

Later, to upgrade Surf, run the command::

    surf self-update

----------------------------
Source composer installation
----------------------------

To install Surf via composer, run the following commands::

    # Until stable release, you need to add stability-flag @beta
    composer global require typo3/surf:^2.0.0@beta

This way, you can add `~/.composer/vendor/typo3/surf` to `PHP Include Paths` of your IDE.

Read _`GettingStarted/Index` next.
