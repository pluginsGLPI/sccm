FR-Synchronisation des données avec l'outil Microsoft SCCM 2012 R2
===

Plugin permettant de synchroniser les ordinateurs présents dans SCCM (version 1802) avec GLPI (version 9.2).

Il utilise le serveur FusionInventory for GLPI et la puissance de son moteur interne.

# Principe de fonctionnement

* Le plugin intègre deux actions automatiques : "SCCMCollect" et "SCCMPush".

* L'action automatique "SCCMCollect" interroge le serveur SCCM au moyen de requêtes MsSQL.

* Cette même action construit un XML au format FusionInventory.

* L'action automatique "SCCMPush" injecte les fichiers XML en HTTP(s) (via cURL) pour faire appaître les ordinateurs dans GLPI.

De la même manière que le ferait un agent FusionInventory.

![GLPISCCMPluginSchema](/screenshots/schema.png "GLPISCCMPluginSchema")

# Pré-requis

* Plugin FusionInventory for GLPI : https://github.com/fusioninventory/fusioninventory-for-glpi

* PHP *curl_init* : http://php.net/manual/fr/function.curl-init.php

* PHP *sqlsrv_connect* : http://php.net/manual/fr/function.sqlsrv-connect.php

* Microsoft System Center 2012 R2 Configuration Manager : https://www.microsoft.com/fr-fr/cloud-platform/system-center-configuration-manager

* Microsoft Drivers for PHP for Microsoft SQL Server : https://github.com/Microsoft/msphpsql

# Contribuer

* Respectez les [directives de développement](http://glpi-developer-documentation.readthedocs.io/en/master/plugins/index.html)

* Reportez-vous au processus [GitFlow](http://git-flow.readthedocs.io/fr/latest/) pour la gestion des branches

* Travaillez sur une nouvelle branche de votre fork

* Soumettez une PR qui sera analysé par un développeur

# Captures d'écran

Configurations du plugin (Configuration => Connecteur SCCM) :
![Formulaire de configuration de SCCM](/screenshots/Config_SCCM.png "Formulaire de configuration de SCCM")

Visualisation des actions automatiques (Configuration => Actions automatiques) :
![Actions automatiques SCCM](/screenshots/auto_task.png "Actions automatiques SCCM")


EN-Data synchronization with Microsoft SCCM 2012 R2 tool
===

Plugin to synchronize computers from SCCM (version 1802) to GLPI (version 9.2).

It uses the "FusionInventory for GLPI" plugin and the power of its internal engine :

# Workflow

* The plugin integrates two automatic actions : "SCCMCollect" et "SCCMPush".

* The automatic action "SCCMCollect" queries the SCCM server with MsSQL queries.

* This same action builds an XML foreach computer (in FusionInventory format).

* The automatic action "SCCMPush" injects XML files into GLPI over HTTP(s) (via cURL and FusionInventory) to display computer in GLPI.

This is the same workflow that FusionInventory agent.

![GLPISCCMPluginSchema](/screenshots/schema.png "GLPISCCMPluginSchema")

# Prerequisite

* FusionInventory for GLPI : https://github.com/fusioninventory/fusioninventory-for-glpi

* PHP *curl_init* : http://php.net/manual/en/function.curl-init.php

* PHP *sqlsrv_connect* : http://php.net/manual/en/function.sqlsrv-connect.php

* Microsoft System Center 2012 R2 Configuration Manager : http://www.microsoft.com/en-gb/server-cloud/products/system-center-2012-r2/default.aspx

# Contributing

* Follow [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/master/plugins/index.html)

* Refer to [GitFlow](http://git-flow.readthedocs.io/en/latest/) process for branching

* Work on a new branch on your own fork

* Open a PR that will be reviewed by a developer

# Screenshots

Plugin configurations (Setup => SCCM Connector) :
![SCCM configuration form](/screenshots/Config_SCCM.png "SCCM configuration form")

Displaying automatic actions (Setup => Automatic actions) :
![SCCM automatic actions](/screenshots/auto_task.png "SCCM automatic actions")


Licence for this plugin
===

GLPISCCMPlugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GLPISCCMPlugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPISCCMPlugin. If not, see <http://www.gnu.org/licenses/>.



Logo by @iconmonstr: http://iconmonstr.com/

