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

Session::checkRight("config", UPDATE);

$config = new PluginSccmConfig();

if (isset($_POST["update"])) {
   $config->update($_POST);
   $sccmDB = new PluginSccmSccmdb();
   $sccmDB->testConfiguration($_POST['id']);
   Html::back();
} else if (isset($_POST["add"])) {
   if ($config->add($_POST)) {
      if ($_SESSION['glpibackcreated']) {
          Html::redirect($track->getLinkURL());
      }
   }
   Html::back();
} else if (isset($_POST["purge"])) {
   $config->delete($_POST, 1);
   $config->redirectToList();
}

$menus = ['config', PluginSccmMenu::class];
PluginSccmConfig::displayFullPageForItem($_GET['id'], $menus, $_GET);
