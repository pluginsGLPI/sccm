<?php

/**
 * -------------------------------------------------------------------------
 * SCCM plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of SCCM.
 *
 * SCCM is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * SCCM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SCCM. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2014-2023 by SCCM plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/sccm
 * -------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');
require_once('../inc/config.class.php');

global $CFG_GLPI;

$urlConfigList = PluginSccmConfig::configUrl();

Session::checkRight("config", UPDATE);

$PluginSccmConfig = new PluginSccmConfig();
global $DB;

if (isset($_POST["update"])) {
   if (array_key_exists('sccmdb_password', $_POST)) {
      // Password must not be altered.
      $_POST['sccmdb_password'] = $_UPOST['sccmdb_password'];
   }

   $PluginSccmConfig->update($_POST);

   Toolbox::logInFile('sccm', "Updating configuration ".$_POST['sccm_config_name']." ".$_POST['id']." ...\n", true);
   
   $sccmDB = new PluginSccmSccmdb();
   $sccmDB->testConfiguration($_POST['id']);

   Html::redirect($urlConfigList);
}
if (isset($_POST["add"])) {
   Toolbox::logInFile('sccm', "Inserting configuration ".$_POST['sccm_config_name']." ...\n", true);
   $insertedId = $PluginSccmConfig->add($_POST);
   
   if ($insertedId) {
      $sccmDB = new PluginSccmSccmdb();
      $sccmDB->testConfiguration($insertedId);
   } else {
      Toolbox::logInFile('sccm', "Error inserting configuration ".$_POST['sccm_config_name']." ".$DB->error()." ...\n", true);
      Session::addMessageAfterRedirect("Error inserting configuration.", false, ERROR, false);   
   }

   Html::redirect($urlConfigList);
}

Html::header(__("Setup - SCCM", "sccm"), $_SERVER["PHP_SELF"],
             "config", "sccm", "configuration");
$configId = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($configId !== null) {
   $PluginSccmConfig->showConfigForm($PluginSccmConfig, $configId);
} else {
   Search::show('PluginSccmConfig');
}

Html::footer();