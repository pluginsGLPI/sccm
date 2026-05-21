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

Session::checkRight(PluginSccmConfig::$rightname, UPDATE);
$config = new PluginSccmConfig();

if (isset($_POST['add'])) {
    $config->check(-1, CREATE, $_POST);
    if (($newID = $config->add($_POST)) && $_SESSION['glpibackcreated']) {
        Html::redirect($config->getLinkURL());
    }

    Html::back();
} elseif (isset($_POST['purge'])) {
    $config->check($_POST['id'], PURGE);
    $config->delete($_POST, true);
    $config->redirectToList();
} elseif (isset($_POST['update'])) {
    $config->check($_POST['id'], UPDATE);
    $config->update($_POST);

    $sccm_db = new PluginSccmSccmdb();
    if ($sccm_db->connect((int) $_POST['id'])) {
        Session::addMessageAfterRedirect(__s("Login successful", "sccm"), false, INFO, false);
    } else {
        Session::addMessageAfterRedirect(__s("Incorrect login", "sccm"), false, ERROR, false);
    }

    Html::back();
} elseif (isset($_POST['test_connection'])) {
    $config->check($_POST['id'], READ);
    $sccm_db = new PluginSccmSccmdb();
    if ($sccm_db->connect((int) $_POST['id'])) {
        $sccm_db->disconnect();
        Session::addMessageAfterRedirect(__s('Connection successful!', 'sccm'), false, INFO);
    } else {
        Session::addMessageAfterRedirect(__s('Connection failed. Check your settings.', 'sccm'), false, ERROR);
    }

    Html::back();
} else {
    Html::header(
        PluginSccmConfig::getTypeName(Session::getPluralNumber()),
        '',
        'config',
        'PluginSccmMenu',
    );

    $config->display(['id' => (int) ($_GET['id'] ?? -1)]);

    Html::footer();
}
