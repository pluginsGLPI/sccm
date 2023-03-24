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

class PluginSccmMenu extends CommonGLPI {

   static function getTypeName($nb = 0) {
      return __('SCCM Connector', 'sccm');
   }

   static function getMenuName() {
      return __('SCCM Connector', 'sccm');
   }

   static function getMenuContent() {
      global $CFG_GLPI;
      $menu          = [];
      $menu['title'] = self::getMenuName();
      $menu['page']  = '/' . Plugin::getWebDir('sccm', false) . '/front/config.form.php';

      if (Session::haveRight('config', UPDATE)) {

         $menu['options']['model']['title'] = self::getTypeName();

      }

      return $menu;
   }

}