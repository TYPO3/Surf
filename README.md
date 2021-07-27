![Tests](https://github.com/TYPO3/Surf/workflows/Tests/badge.svg?branch=master&event=push)
[![Coverage Status](https://img.shields.io/coveralls/TYPO3/Surf/master.svg?style=flat-square)](https://coveralls.io/github/TYPO3/Surf?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/TYPO3/Surf.svg?style=flat-square)](https://packagist.org/packages/TYPO3/Surf)

# Powerful and flexible deployment tool for PHP projects

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

## Documentation

For further information please read the documentation https://docs.typo3.org/other/typo3/surf/master/en-us/.

### Contributing to the documentation

You can simply edit or add a .rst file in the `Documentation` folder on Github and create a pull request.

The online documentation will automatically update after changes to the master branch.
To preview the documentation locally please follow this [guide](https://github.com/t3docs/docker-render-documentation).

The documentation was set up according to the [TYPO3 documentation guide](https://docs.typo3.org/typo3cms/RenderTYPO3DocumentationGuide/Index.html).

## Copyright

The deployment package is licensed under GNU General Public License, version 3 or later
(http://www.gnu.org/licenses/gpl.html). Initial development was sponsored by
[networkteam - Flow Framework Agentur](https://networkteam.com/fokus/flow-framework.html).
