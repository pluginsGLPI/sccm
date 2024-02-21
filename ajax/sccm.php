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

include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

if (!isset($_POST['action'])) {
   exit;
}

switch ($_POST['action']) {
    case "get_dropdown_number":
        echo Dropdown::showNumber('param', ['value' => $_POST['value'],
            'min'   => 100,
            'step'  => 100,
            'max'   => 1000,
            'display' => false,
        ]);
        break;
    case "get_dropdown_frequency":
        $tab = [];

        for ($i = 15; $i < 60; $i += 15) {
            $tab[$i * MINUTE_TIMESTAMP] = sprintf(_n('%d minute', '%d minutes', $i), $i);
        }

        for ($i = 1; $i < 24; $i++) {
            $tab[$i * HOUR_TIMESTAMP] = sprintf(_n('%d hour', '%d hours', $i), $i);
        }

        $tab[DAY_TIMESTAMP] = __('Each day');
        for ($i = 2; $i < 7; $i++) {
            $tab[$i * DAY_TIMESTAMP] = sprintf(_n('%d day', '%d days', $i), $i);
        }

        $tab[WEEK_TIMESTAMP]  = __('Each week');
        $tab[MONTH_TIMESTAMP] = __('Each month');

        echo Dropdown::showFromArray("frequency", $tab, ['value' => $_POST['value'], 'display' => false]);
        break;
}
