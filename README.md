# SCCM Plugin For GLPI

[![License](https://img.shields.io/github/license/pluginsGLPI/sccm.svg?&label=License&style=for-the-badge)](https://github.com/pluginsGLPI/sccm/blob/main/LICENSE)
![Static Badge](https://img.shields.io/badge/Project_Status-Active-green?style=for-the-badge)
![GitHub Actions Status](https://img.shields.io/github/actions/workflow/status/pluginsGLPI/sccm/continuous-integration.yml?style=for-the-badge)
[![GitHub release](https://img.shields.io/github/release/pluginsGLPI/sccm.svg?&style=for-the-badge)](https://github.com/pluginsGLPI/sccm/releases)
![Github Download](https://img.shields.io/github/downloads/pluginsGLPI/sccm/total?style=for-the-badge)


<p align="center">
  <img width="126" height="126" src="https://raw.githubusercontent.com/pluginsGLPI/sccm/refs/heads/main/screenshots/logo.png">
</p>

## 📌 Overview

The **SCCM** plugin allows you to automatically synchronize computers managed by Microsoft System Center Configuration Manager (SCCM) with your **GLPI** inventory.

It operates in two phases:

1. **Collection**: Reading information from the SCCM database (via the `sqlsrv` PHP extension) and generating an XML file for each workstation.
2. **Injection**: Sending these XML files to the GLPI inventory (`front/inventory.php`) via `cURL`.

## ✅ Prerequisites


* PHP extension `sqlsrv` (Microsoft Drivers for PHP for SQL Server [installation guide](https://learn.microsoft.com/fr-fr/sql/connect/php/installation-tutorial-linux-mac?view=sql-server-ver17))
* PHP extension `curl`


## ▶️ How It Works

The plugin automatically creates two GLPI cron tasks (Automatic Actions) during installation:

* **SCCMCollect** (Data collection and XML generation) — Scheduled by default between 04:00 and 05:00.
* **SCCMPush** (Sending XMLs to `front/inventory.php`) — Scheduled by default between 06:00 and 07:00.

These tasks can be managed in **Setup > Automatic actions**.

![GLPISCCMPluginSchema](screenshots/schema.png "GLPISCCMPluginSchema")

## 🧩 Synchronized Data

The SCCM collection retrieves the following data:

* System information (Machine, BIOS, CPU, RAM, Disks)
* IP / MAC addresses and network interfaces
* Installed software
* Users / Sessions
* Hardware status (LastHWScan)


## 📚 Documentation

Full technical and user guide: [GLPI Plugins - SCCM](https://help.glpi-project.org/doc-plugins/plugins-glpi/sccm)

## 💼 Professional Services

GLPI Network services are available through our [Partner Network](http://www.teclib-edition.com/en/partners/).
We offer specialized training, bug fixes with an editor subscription, contributions for new features, and much more.

Benefit from a personalized service experience, complete with exclusive advantages and opportunities.

## 🤝 Contributing

* Bug reports and feature requests are welcome! Please open an issue or submit a PR to start a discussion
* Follow the [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins/index.html).
* Refer to the [GitFlow](http://git-flow.readthedocs.io/) process for branching.
* Work on a new branch within your own fork.
* Open a Pull Request (PR) to be reviewed by a developer.

## 🚀 About

![Teclib Branding](screenshots/teclib_branding.png "Teclib Branding")

[![X](https://img.shields.io/badge/X-%23000000.svg?style=flat&logo=X&logoColor=white)](https://x.com/GLPI_PROJECT)
[![Facebook](https://img.shields.io/badge/Facebook-%231877F2.svg?style=flat&logo=Facebook&logoColor=white)](https://www.facebook.com/glpiproject)
[![LinkedIn](https://img.shields.io/badge/linkedin-%230077B5.svg?style=flat&logo=linkedin&logoColor=white)](https://www.linkedin.com/company/teclib/)
[![Reddit](https://img.shields.io/badge/Reddit-%23FF4500.svg?style=flat&logo=Reddit&logoColor=white)](https://www.reddit.com/r/glpi/)
[![Telegram](https://img.shields.io/badge/Telegram-2CA5E0?style=flat&logo=telegram&logoColor=white)](https://t.me/glpien)
[![YouTube](https://img.shields.io/badge/YouTube-%23FF0000.svg?style=flat&logo=YouTube&logoColor=white)](https://www.youtube.com/@glpi-network/featured)
