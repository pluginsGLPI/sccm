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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

use Glpi\Toolbox\Sanitizer;

class PluginSccmInventoryLog
{
    static function install(Migration $migration)
    {
        global $DB;

        $table = 'glpi_plugin_sccm_inventorylogs';

        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");
            $query = "CREATE TABLE `ma_table` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `itemtype` varchar(255) NOT NULL,
                `items_id` int(11) NOT NULL,
                `error` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $DB->queryOrDie($query, $DB->error());
        }
        $migration->migrationOneTable($table);

        return true;
    }


    static function uninstall()
    {
        global $DB;

        if ($DB->tableExists('glpi_plugin_sccm_inventorylogs')) {

            $query = "DROP TABLE `glpi_plugin_sccm_inventorylogs`";
            $DB->queryOrDie($query, $DB->error());
        }
        return true;
    }
}
