# SCCM Plugin For GLPI

![GLPI Banner](https://user-images.githubusercontent.com/29282308/31666160-8ad74b1a-b34b-11e7-839b-043255af4f58.png)

[![License](https://img.shields.io/github/license/pluginsGLPI/sccm.svg?&label=License&style=for-the-badge)](https://github.com/pluginsGLPI/sccm/blob/main/LICENSE)
![Static Badge](https://img.shields.io/badge/Project_Status-Active-green?style=for-the-badge)
![GitHub Actions Status](https://img.shields.io/github/actions/workflow/status/pluginsGLPI/sccm/continuous-integration.yml?style=for-the-badge)
[![GitHub release](https://img.shields.io/github/release/pluginsGLPI/sccm.svg?&style=for-the-badge)](https://github.com/pluginsGLPI/sccm/releases)
![GitHub Downloads](https://img.shields.io/github/downloads/pluginsGLPI/sccm/total?style=for-the-badge)

<p align="center">
  <img width="126" height="126" src="https://raw.githubusercontent.com/pluginsGLPI/sccm/refs/heads/update_repo/screenshots/logo.png">
</p>


## 📌 Overview

The **SCCM** plugin for GLPI allows automatic synchronization of computers managed by **Microsoft System Center Configuration Manager (SCCM)** with your GLPI inventory.

It works in **two main phases**:

1. **Collection**: Reads information from the SCCM database (via the `sqlsrv` PHP extension) and generates an XML file for each asset.
2. **Injection**: Sends these XML files to GLPI native inventory via `cURL`.


## ✅ Prerequisites

Before installing, ensure the following:

- PHP extension `sqlsrv`
  [Installation guide for Microsoft Drivers for PHP for SQL Server](https://learn.microsoft.com/fr-fr/sql/connect/php/installation-tutorial-linux-mac?view=sql-server-ver17)
- PHP extension `curl`


## ▶️ How It Works

The plugin automatically creates **two GLPI cron tasks** (Automatic Actions) during installation:

- **SCCMCollect** – Collects data and generates XMLs (default schedule: 04:00–05:00)
- **SCCMPush** – Sends XMLs to GLPI (`front/inventory.php`) (default schedule: 06:00–07:00)

You can manage these tasks under **Setup > Automatic actions**.

![GLPI SCCM Plugin Schema](screenshots/schema.png "GLPISCCMPluginSchema")


## 🧩 Synchronized Data

The plugin collects the following information from SCCM:

- System information: Machine, BIOS, CPU, RAM, Disks
- Network interfaces: IP / MAC addresses
- Installed software
- Users and sessions
- Hardware


## 📚 Documentation

Full plugin documentation is available [here](https://help.glpi-project.org/doc-plugins/plugins-glpi/sccm).


## 💼 Professional Services

GLPI professional services are offered through the [Partner Network](http://www.teclib-edition.com/en/partners/):

- Specialized training
- Bug fixes with an editor subscription
- Contributions for new features
- Personalized support and consulting

Experience a tailored service with exclusive advantages and opportunities.


## 🤝 Contributing

We welcome contributions! Here's how you can help:

- Report bugs or request features via [Issues](https://github.com/pluginsGLPI/sccm/issues)
- Follow the [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins/index.html)
- Use [GitFlow](http://git-flow.readthedocs.io/) for branching
- Work on a new branch in your fork
- Submit a Pull Request (PR) for review


## 🚀 About

![GLPI Banner](https://user-images.githubusercontent.com/29282308/31666160-8ad74b1a-b34b-11e7-839b-043255af4f58.png)

![Teclib Banner](screenshots/teclib_branding.png)


## 📣 Connect with GLPI

[![Facebook GLPI](https://img.shields.io/badge/Facebook-GLPI-1877F2.svg?style=for-the-badge)](https://www.facebook.com/glpiproject/)
[![X (formerly Twitter)](https://img.shields.io/badge/Twitter-GLPI%20Project-26A2FA.svg?style=for-the-badge)](https://x.com/GLPI_PROJECT)
[![YouTube GLPI](https://img.shields.io/badge/YouTube-GLPI-FF0033.svg?style=for-the-badge)](https://www.youtube.com/channel/UCoIMi7aKeIvQRxi7ggd6VNA)
[![Instagram GLPI](https://img.shields.io/badge/Instagram-GLPI-E1306C.svg?style=for-the-badge)](https://www.instagram.com/glpi_project/)
[![LinkedIn GLPI](https://img.shields.io/badge/LinkedIn-GLPI-0A66C2.svg?style=for-the-badge)](https://www.linkedin.com/products/teclib-glpi/)
[![Telegram GLPI](https://img.shields.io/badge/Telegram-GLPI-blue.svg?style=for-the-badge)](https://t.me/glpien)
