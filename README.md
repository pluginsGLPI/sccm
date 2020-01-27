# Data synchronization with Microsoft SCCM tool

[![License](https://img.shields.io/github/license/pluginsGLPI/sccm.svg?&label=License)](https://github.com/pluginsGLPI/sccm/blob/develop/LICENSE)
[![Follow twitter](https://img.shields.io/twitter/follow/Teclib.svg?style=social&label=Twitter&style=flat-square)](https://twitter.com/teclib)
[![Telegram Group](https://img.shields.io/badge/Telegram-Group-blue.svg)](https://t.me/glpien)
[![Project Status: Active](http://www.repostatus.org/badges/latest/active.svg)](http://www.repostatus.org/#active)
[![GitHub release](https://img.shields.io/github/release/pluginsGLPI/sccm.svg)](https://github.com/pluginsGLPI/sccm/releases)
[![GitHub build](https://travis-ci.org/pluginsGLPI/sccm.svg?)](https://travis-ci.org/pluginsGLPI/sccm/)


![GLPISCCMPluginSchema](screenshots/sccm.png "sccm")

Plugin to synchronize computers from SCCM (version 1802) to GLPI.
It uses the "FusionInventory for GLPI" plugin and the power of its internal engine:

### Workflow

* The plugin integrates two automatic actions : "SCCMCollect" et "SCCMPush".
* The automatic action "SCCMCollect" queries the SCCM server with MsSQL queries.
* This same action builds an XML foreach computer (in FusionInventory format).
* The automatic action "SCCMPush" injects XML files into GLPI over HTTP(s) (via cURL and FusionInventory) to display computer in GLPI.

This is the same workflow that FusionInventory agent.

![GLPISCCMPluginSchema](screenshots/schema.png "GLPISCCMPluginSchema")


## Documentation

We maintain a detailed documentation here -> [Documentation](https://glpi-plugins.readthedocs.io/en/latest/sccm/index.html)

## Contact

For notices about major changes and general discussion of sccm, subscribe to the [/r/glpi](https://www.reddit.com/r/glpi/) subreddit.
You can also chat with us via IRC in [#glpi on freenode](http://webchat.freenode.net/?channels=glpi) or [@glpi on Telegram](https://t.me/glpien).

## Professional Services

![GLPI Network](./glpi_network.png "GLPI network")

The GLPI Network services are available through our [Partner's Network](http://www.teclib-edition.com/en/partners/). We provide special training, bug fixes with editor subscription, contributions for new features, and more.

Obtain a personalized service experience, associated with benefits and opportunities.

## Contributing

* Open a ticket for each bug/feature so it can be discussed
* Follow [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins/index.html)
* Refer to [GitFlow](http://git-flow.readthedocs.io/) process for branching
* Work on a new branch on your own fork
* Open a PR that will be reviewed by a developer

## Copying

* **Code**: you can redistribute it and/or modify
