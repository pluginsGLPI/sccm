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

include(__DIR__ . '/../../../inc/includes.php');
require_once(__DIR__ . '/../inc/config.class.php');

global $CFG_GLPI;

Session::checkRight("config", UPDATE);

//$config = new PluginSccmConfig();
$config = new PluginSccmConfig();
global $DB;

if (isset($_POST["update"])) {
    $config->update($_POST);
    $sccm_db = new PluginSccmSccmdb();
   $config->update($_POST);

   Toolbox::logInFile('sccm', "Updating configuration ".$_POST['sccm_config_name']." ".$_POST['id']." ...\n", true);
   
   $sccmDB = new PluginSccmSccmdb();
   $sccmDB->testConfiguration($_POST['id']);

   Html::redirect(PluginSccmConfig::searchUrl());
} else if (isset($_POST["add"])) {
   Toolbox::logInFile('sccm', "Inserting configuration ".$_POST['sccm_config_name']." ...\n", true);
   $insertedId = $config->add($_POST);
   
   if ($insertedId) {
      $sccm_db = new PluginSccmSccmdb();
      $sccm_db->testConfiguration($insertedId);
   } else {
      Toolbox::logInFile('sccm', "Error inserting configuration ".$_POST['sccm_config_name']." ".$DB->error()." ...\n", true);
      Session::addMessageAfterRedirect("Error inserting configuration.", false, ERROR, false);   
   }

   Html::redirect(PluginSccmConfig::searchUrl());
} else if (isset($_POST["purge"])) {   
   $config->delete($_POST, 1);
   Html::redirect(PluginSccmConfig::searchUrl());
}

$menus = ['config', PluginSccmMenu::class];
PluginSccmConfig::displayFullPageForItem($_GET['id'], $menus, $_GET);
