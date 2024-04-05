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
      return __('Menu', 'sccm');
   }

   static function getMenuName() {
      return __('SCCM', 'sccm');
   }

   static function getMenuContent() {
      $menu = [
         'title'   => PluginSccmConfig::getMenuName(),
         'page'    => PluginSccmConfig::getSearchURL(false),
         'icon'    => PluginSccmConfig::getIcon(),
         'options' => [],
      ];

      $menu['links']['add'] = PluginSccmConfig::getFormURL(false);
      return $menu;
   }

}
