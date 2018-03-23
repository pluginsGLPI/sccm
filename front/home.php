<?php
/*
 *
 -------------------------------------------------------------------------
 GLPISCCMPlugin
 Copyright (C) 2013 by teclib.

 http://www.teclib.com
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPISCCMPlugin.

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
 --------------------------------------------------------------------------
*/

// Original Author of file: François Legastelois <flegastelois@teclib.com>
// ----------------------------------------------------------------------

include ('../../../inc/includes.php');

Session::haveRight("config", UPDATE);

if (!function_exists('curl_init')) {
   echo "cURL extension (PHP) is required... !! \n";
   exit;
}

if (!function_exists('mssql_connect') && !function_exists('sqlsrv_connect')) {
   echo "MS-SQL extension (PHP) is required... !! \n";
   exit;
}

if (isset($argv)) {
   for ($i=1; $i<count($argv); $i++) {
      //To be able to use = in search filters, enter \= instead in command line
      //Replace the \= by ° not to match the split function
      $arg   = str_replace('\=', '°', $argv[$i]);
      $it    = explode("=", $arg);
      $it[0] = preg_replace('/^--/', '', $it[0]);

      //Replace the ° by = the find the good filter
      $it           = str_replace('°', '=', $it);
      $_GET[$it[0]] = $it[1];
   }
}

$REP_XML = GLPI_PLUGIN_DOC_DIR.'/sccm/xml/';

$PluginSccmConfig = new PluginSccmConfig();
$PluginSccmConfig->getFromDB(1);

$PluginSccmSccm = new PluginSccmSccm();

$PluginSccmSccmdb = new PluginSccmSccmdb();
$PluginSccmSccmdb->connect();

$action = isset($_GET['task']) ? $_GET['task'] : "home";

if (!in_array($action, array('home','test','showtable'))) {
   die('Erreur');
}

switch ($action) {
   case 'test':
      include('test.php');
   break;
   case 'showtable' :
      include('showtable.php');
   break;
   case 'home':
      $PluginSccmSccm->showHome();
   break;
}

$PluginSccmSccmdb->disconnect();