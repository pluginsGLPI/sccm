FR-Synchronisation des données avec l'outil Microsoft SCCM 2012 R2
===

Plugin permettant de synchroniser les ordinateurs présents dans SCCM avec GLPI.

Il utilise le serveur FusionInventory for GLPI et la puissance de son moteur interne.

# Schéma de principe

* Le plugin interroge le serveur SCCM au moyen de requêtes MsSQL

* il construit un XML au format FusionInventory (avec ou sans écriture sur disque)

* et l'injecte directement en HTTP (via cURL)

De la même manière que le ferait un agent FusionInventory.

![GLPISCCMPluginSchema](/screenshots/schema.png "GLPISCCMPluginSchema")

# Pré-requis

* Plugin FusionInventory for GLPI : http://www.fusioninventory.org/documentation/fi4g/installation/

* PHP *curl_init* : http://php.net/manual/fr/function.curl-init.php

* PHP *mssql_connect* : http://php.net/manual/fr/function.mssql-connect.php

* Microsoft System Center 2012 R2 Configuration Manager : http://www.microsoft.com/fr-fr/server-cloud/products/system-center-2012-r2/default.aspx


EN-Data synchronization with Microsoft SCCM 2012 R2 tool
===

Plugin to synchronize computers from SCCM to GLPI.

It uses the "FusionInventory for GLPI" plugin and the power of its internal engine :

# Workflow

* The plugin ask the SCCM server with MsSQL queries ;

* he builds an XML foreach computer (in FusionInventory format) ;

* and injects it directly into GLPI over HTTP(s) (via cURL and FusionInventory).

This is the same workflow that FusionInventory agent.

![GLPISCCMPluginSchema](/screenshots/schema.png "GLPISCCMPluginSchema")

# Prerequisite

* FusionInventory for GLPI : http://www.fusioninventory.org/documentation/fi4g/installation/

* PHP *curl_init* : http://php.net/manual/en/function.curl-init.php

* PHP *mssql_connect* : http://php.net/manual/en/function.mssql-connect.php

* Microsoft System Center 2012 R2 Configuration Manager : http://www.microsoft.com/en-gb/server-cloud/products/system-center-2012-r2/default.aspx


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

