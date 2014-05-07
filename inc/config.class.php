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
 
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginSccmConfig extends CommonDBTM {

   static private $_instance = NULL;

   static function canCreate() {
      return Session::haveRight('config', 'w');
   }


   static function canView() {
      return Session::haveRight('config', 'r');
   }


   static function getTypeName($nb=0) {
      return __("Setup - SCCM", "sccm");
   }


   function getName($with_comment=0) {
      return __("Interface - SCCM", "sccm");
   }

   static function getInstance() {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
         if (!self::$_instance->getFromDB(1)) {
            self::$_instance->getEmpty();
         }
      }
      return self::$_instance;
   }


   function prepareInputForUpdate($input) {
      if (isset($input["sccmdb_password"]) AND !empty($input["sccmdb_password"])) {
         $input["sccmdb_password"] = Toolbox::encrypt(stripslashes($input["sccmdb_password"]), GLPIKEY);
      }

      return $input;
   }

   static function install(Migration $mig) {
      global $DB;

      $table = 'glpi_plugin_sccm_configs';

      if (!TableExists($table)) {

         $query = "CREATE TABLE `". $table."`(
                     `id` int(11) NOT NULL,
                     `sccmdb_host` VARCHAR(255) NULL,
                     `sccmdb_dbname` VARCHAR(255) NULL,
                     `sccmdb_user` VARCHAR(255) NULL,
                     `sccmdb_password` VARCHAR(255) NULL,
                     `fusioninventory_url` VARCHAR(255) NULL,
                     `active_sync` tinyint(1) NOT NULL default '0',
                     `date_mod` datetime default NULL,
                     `comment` text,
                     PRIMARY KEY  (`id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

         $DB->queryOrDie($query, __("Error when using glpi_plugin_sccm_configs table.", "sccm") 
                              . "<br />".$DB->error());

         $sccmdb_password = Toolbox::encrypt("glpi00",GLPIKEY);

         $query = "INSERT INTO `$table`
                         (id, date_mod, sccmdb_host, sccmdb_dbname, 
                           sccmdb_user, sccmdb_password, fusioninventory_url)
                   VALUES (1, NOW(), 'lin1e7.tlt','facdb','glpi','".$sccmdb_password."',
                           'http://glpi/plugins/fusioninventory/front/communication.php')";

         $DB->queryOrDie($query, __("Error when using glpi_plugin_sccm_configs table.", "sccm") 
                                 . "<br />" . $DB->error());
      }

      return true;
   }


   static function uninstall() {
      global $DB;

      if (TableExists('glpi_plugin_sccm_configs')) {

         $query = "DROP TABLE `glpi_plugin_sccm_configs`";
         $DB->queryOrDie($query, $DB->error());
      }
      return true;
   }


   static function showConfigForm($item) {
      $config = self::getInstance();

      $config->showFormHeader();

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Enable SCCM synchronization", "sccm")."</td><td>";
      Dropdown::showYesNo("active_sync", $config->getField('active_sync'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Server hostname (MSSQL)", "sccm")."</td><td>";
      Html::autocompletionTextField($config,'sccmdb_host');
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Database name", "sccm")."</td><td>";
      Html::autocompletionTextField($config,'sccmdb_dbname');
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Username", "sccm")."</td><td>";
      Html::autocompletionTextField($config,'sccmdb_user');
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Password", "sccm")."</td><td>";
      echo "<input type='password' name='sccmdb_password' value='' autocomplete='off'>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("URL FusionInventory for injection", "sccm")."</td><td>";
      Html::autocompletionTextField($config,'fusioninventory_url');
      echo "</td></tr>\n";

      $config->showFormButtons(array('candel'=>false));

      return false;
   }

}
