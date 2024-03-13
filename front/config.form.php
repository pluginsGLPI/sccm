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

Session::checkRight("config", UPDATE);

$PluginSccmConfig = new PluginSccmConfig();
$sccmDB = new PluginSccmSccmdb();
global $DB;

if (isset($_POST["update"])) {
   if (array_key_exists('sccmdb_password', $_POST)) {
      // Password must not be altered.
      $_POST['sccmdb_password'] = $_UPOST['sccmdb_password'];
   }

   $PluginSccmConfig->update($_POST);
   $sccmDB->testConfiguration($_POST['id']);
   Html::redirect(PluginSccmConfig::searchUrl());
} else if (isset($_POST["add"])) {
   if ($PluginSccmConfig->add($_POST)) {
      if ($_SESSION['glpibackcreated']) {
          Html::redirect($track->getLinkURL());
      }
   }
   Html::back();
} else if (isset($_POST["purge"])) {
   $PluginSccmConfig->delete($_POST, 1);
   $PluginSccmConfig->redirectToList();
}

Html::header(PluginSccmConfig::getTypeName(), $_SERVER["PHP_SELF"], "config", PluginSccmMenu::class, "configuration");
$PluginSccmConfig->display($_GET);
Html::footer();
