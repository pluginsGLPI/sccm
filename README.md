# Data synchronization with Microsoft SCCM tool

![GLPI Banner](https://user-images.githubusercontent.com/29282308/31666160-8ad74b1a-b34b-11e7-839b-043255af4f58.png)

[![License GPL 3.0](https://img.shields.io/badge/License-GPL%203.0-blue.svg)](https://github.com/pluginsGLPI/sccm/blob/develop/LICENSE.md)
[![Telegram GLPI](https://img.shields.io/badge/Telegram-GLPI-blue.svg)](https://t.me/glpien)
[![IRC Chat](https://img.shields.io/badge/IRC-%23GLPI-green.svg)](http://webchat.freenode.net/?channels=GLPI)
[![Follow Twitter](https://img.shields.io/badge/Twitter-GLPI%20Project-26A2FA.svg)](https://twitter.com/GLPI_PROJECT)
[![Project Status: Active](http://www.repostatus.org/badges/latest/active.svg)](http://www.repostatus.org/#active)
[![Conventional Commits](https://img.shields.io/badge/Conventional%20Commits-1.0.0-yellow.svg)](https://conventionalcommits.org)

Extend GLPI with Plugins.

## Table of Contents

* [Synopsis](#synopsis)
* [Build Status](#build-status)
* [Documentation](#documentation)
* [Versioning](#versioning)
* [Contact](#contact)
* [Contribute](#contribute)
* [Copying](#copying)

## Synopsis

Plugin to synchronize computers from SCCM (version 1802) to GLPI (version 9.2).
It uses the "FusionInventory for GLPI" plugin and the power of its internal engine:

### Workflow

* The plugin integrates two automatic actions : "SCCMCollect" et "SCCMPush".
* The automatic action "SCCMCollect" queries the SCCM server with MsSQL queries.
* This same action builds an XML foreach computer (in FusionInventory format).
* The automatic action "SCCMPush" injects XML files into GLPI over HTTP(s) (via cURL and FusionInventory) to display computer in GLPI.

This is the same workflow that FusionInventory agent.

![GLPISCCMPluginSchema](screenshots/schema.png "GLPISCCMPluginSchema")

## Build Status

|**LTS**|Bleeding Edge|
|:---:|:---:|
|[![Travis CI build](https://api.travis-ci.org/pluginsGLPI/sccm.svg?branch=master)](https://travis-ci.org/pluginsGLPI/sccm/)|[![Travis CI build](https://api.travis-ci.org/pluginsGLPI/sccm.svg?branch=develop)](https://travis-ci.org/pluginsGLPI/sccm/)|

## Documentation

We maintain a detailed documentation of the project on the website, check the [How-tos](https://pluginsglpi.github.io/sccm/howtos/) and [Development](https://pluginsglpi.github.io/sccm/) section.

## Versioning

In order to provide transparency on our release cycle and to maintain backward compatibility, this project is maintained under [the Semantic Versioning guidelines](http://semver.org/). We are committed to following and complying with the rules, the best we can.

See [the tags section of our GitHub project](https://github.com/pluginsGLPI/sccm/tags/) for changelogs for each release version. Release announcement posts on [the official Teclib' blog](http://www.teclib-edition.com/en/communities/blog-posts/) contain summaries of the most noteworthy changes made in each release.

## Contact

For notices about major changes and general discussion of development, subscribe to the [/r/glpi](http://www.reddit.com/r/glpi) subreddit.
You can also chat with us via IRC in [#GLPI on freenode](http://webchat.freenode.net/?channels=GLPI) if you get stuck, and [@glpien on Telegram](https://t.me/glpien).

## Contribute

Want to file a bug, contribute some code, or improve documentation? Excellent! Read up on our
guidelines for [contributing](https://github.com/pluginsGLPI/sccm/blob/develop/.github/CONTRIBUTING.md) and then check out one of our issues in the [Issues Dashboard](https://github.com/pluginsGLPI/sccm/issues/).

## Copying

* **Name**: [GLPI](http://glpi-project.org/) is a registered trademark of [Teclib'](http://www.teclib-edition.com/en/).
* **Logo**: by @iconmonstr, [Iconmostr](http://iconmonstr.com/)
* **Code**: you can redistribute it and/or modify it under the terms of the GNU General Public License ([GPLv3](https://www.gnu.org/licenses/gpl-3.0.en.html)).
* **Documentation**: released under Attribution 4.0 International ([CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)).