<?php
/*
 *
 -------------------------------------------------------------------------
 GLPISCCMPlugin
 Copyright (C) 2014 by teclib.

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

function plugin_init_sccm() {
  global $PLUGIN_HOOKS,$CFG_GLPI;

  $plugin = new Plugin();

  $PLUGIN_HOOKS['csrf_compliant']['sccm'] = true;
  $PLUGIN_HOOKS['menu_entry']['sccm']   = false;

   if ($plugin->isActivated("sccm") && Session::getLoginUserID()) {
      if (Session::haveRight("config", UPDATE)) {

         $PLUGIN_HOOKS['config_page']['sccm'] = "front/config.form.php";
         $PLUGIN_HOOKS["menu_toadd"]['sccm'] = array('config' => 'PluginSccmMenu');
      }
   }
}

function plugin_version_sccm() {

   return array('name'          => __("Interface - SCCM", "sccm"),
               'version'        => '9.1.x-1.0.0',
               'author'         => 'TECLIB\'',
               'license'        => 'GPLv2+',
               'homepage'       => 'http://www.teclib.com',
               'minGlpiVersion' => '0.85');
}

function plugin_sccm_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85','lt')) {
      echo "This plugin requires GLPI = 0.85";
      return false;
   }
   return true;
}

function plugin_sccm_check_config($verbose=false) {
   if (!function_exists('curl_init')) {
      echo "cURL extension (PHP) is required.";
      return false;
   }
   if (!function_exists('mssql_connect') && !function_exists('sqlsrv_connect')) {
      echo "MS-SQL extension (PHP) is required.";
      return false;
   }
   return true;
}
