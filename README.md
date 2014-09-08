Interface avec l'outil Microsoft SCCM 2012 R2
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

* Microsoft System Center 2012 R2 Configuration Manager : http://www.microsoft.com/france/systemcenter/sccm/default.mspx
