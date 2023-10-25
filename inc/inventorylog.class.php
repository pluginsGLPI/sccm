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

use Glpi\Application\View\TemplateRenderer;

class PluginSccmInventoryLog extends \CommonDBTM
{

    public $dohistory = true;
    public static $rightname = 'config';

    public const SCCM_STATE_DONE = "sccm-done";
    public const SCCM_STATE_FAIL = "sccm-fail";

    static function install(Migration $migration)
    {
        global $DB;

        $table = PluginSccmInventoryLog::getTable();

        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");
            $query = "CREATE TABLE $table (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `computers_id` int(11) DEFAULT NULL,
                `error` text DEFAULT NULL,
                `state` varchar(15) NOT NULL DEFAULT '0',
                `date_mod` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $DB->queryOrDie($query, $DB->error());
            $migration->updateDisplayPrefs([PluginSccmInventoryLog::class => [4, 5, 6, 7, 8]]);
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

    public static function canCreate()
    {
        return false;
    }


    public static function canPurge()
    {
        return false;
    }

    public static function canDelete()
    {
        return false;
    }

    public static function canUpdate()
    {
        return false;
    }


    public static function getTypeName($nb = 0)
    {
        return __('Inventory Logs', 'sccm');
    }

    public static function getMenuName()
    {
        return self::getTypeName();
    }

    public static function getIcon()
    {
        return "ti ti-file-search";
    }

    public static function showLogs()
    {
        $inventorylogs = new self();
        $results = $inventorylogs->find();
        TemplateRenderer::getInstance()->display(
            '@sccm/inventorylogs.html.twig',
            [
                'results' => $results
            ]
        );

        return true;
    }

    public static function getAllState(): array
    {
        return [
            '' => '-------------',
            self::SCCM_STATE_DONE   => __('Done'),
            self::SCCM_STATE_FAIL   => __('Fail'),
        ];
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }

        switch ($field) {
            case 'state':
                return self::getStateLabel($values[$field]);
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    public static function getStateLabel(string $value): string
    {
        if ($value === "") {
            return NOT_AVAILABLE;
        }

        $all = self::getAllState();
        if (!isset($all[$value])) {
            trigger_error(
                sprintf(
                    'Sccm State %1$s does not exists!',
                    $value
                ),
                E_USER_WARNING
            );
            return NOT_AVAILABLE;
        }
        return $all[$value];
    }

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);

        $params = [
            'canedit'        => false,
            'candel'         => false,
        ];
        TemplateRenderer::getInstance()->display(
            '@sccm/inventorylogs.html.twig',
            [
                'item' => $this,
                'state_list' => self::getAllState(),
                'params' => $params,
            ]
        );

        return true;

    }

    public function rawSearchOptions()
    {
        $options = parent::rawSearchOptions();

        $options[] = [
            'id'            => 2,
            'table'         => self::getTable(),
            'field'         => 'id',
            'name'          => __('ID')
        ];

        $options[] = [
            'id'           => 5,
            'table'        => Computer::getTable(),
            'field'        => 'name',
            'name'         => __('Computer'),
            'linkfield'    => 'computers_id',
            'datatype'     => 'dropdown',
        ];

        $options[] = [
            'id'           => 6,
            'table'        => self::getTable(),
            'field'        => 'error',
            'name'         => __('Error', 'sccm')
        ];

        $options[] = [
            'id'           => 7,
            'table'        => self::getTable(),
            'field'        => 'state',
            'name'         => __('State', 'sccm')
        ];

        $options[] = [
            'id'           => 8,
            'table'        => self::getTable(),
            'field'        => 'date_mod',
            'name'         => __('Date modification', 'sccm')
        ];

        return $options;
    }
}
