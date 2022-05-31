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
 * @copyright Copyright (C) 2014-2022 by SCCM plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/sccm
 * -------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');
require_once('../inc/config.class.php');


Session::checkRight("config", UPDATE);

$PluginSccmConfig = new PluginSccmConfig();

if (isset($_POST["update"])) {
   $PluginSccmConfig->update($_POST);

    $sccmDB = new PluginSccmSccmdb();
   if ($sccmDB->connect()) {
      Session::addMessageAfterRedirect("Connexion réussie !.", false, INFO, false);
   } else {
      Session::addMessageAfterRedirect("Connexion incorrecte.", false, ERROR, false);
   }


   Html::back();
}

Html::header(__("Setup - SCCM", "sccm"), $_SERVER["PHP_SELF"],
             "plugins", "sccm", "configuration");
$PluginSccmConfig->showConfigForm($PluginSccmConfig);
Html::footer();