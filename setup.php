<?php
/**
 * ------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of SCCM plugin.
 *
 * SCCM plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * SCCM plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * ------------------------------------------------------------------------
 * @author    François Legastelois <flegastelois@teclib.com>
 * @copyright Copyright (C) 2014-2018 by Teclib' and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/sccm
 * @link      https://pluginsglpi.github.io/sccm/
 * ------------------------------------------------------------------------
 */

define("PLUGIN_SCCM_VERSION", "2.0.1");

// Minimal GLPI version, inclusive
define("PLUGIN_SCCM_MIN_GLPI", "9.2");
// Maximum GLPI version, exclusive
define("PLUGIN_SCCM_MAX_GLPI", "9.3");

function plugin_init_sccm() {
   global $PLUGIN_HOOKS,$CFG_GLPI;

   $plugin = new Plugin();

   $PLUGIN_HOOKS['csrf_compliant']['sccm'] = true;
   $PLUGIN_HOOKS['menu_entry']['sccm']   = false;

   if ($plugin->isActivated("sccm") && Session::getLoginUserID()) {
      if (Session::haveRight("config", UPDATE)) {

         $PLUGIN_HOOKS['config_page']['sccm'] = "front/config.form.php";
         $PLUGIN_HOOKS["menu_toadd"]['sccm'] = array('config' => 'PluginSccmMenu');
      }
   }
}

/**
 * function to define the version for glpi for plugin
 *
 * @return array
 */
function plugin_version_sccm() {

   return [
      'name' => __("Interface - SCCM", "sccm"),
      'version' => PLUGIN_SCCM_VERSION,
      'author'  => 'TECLIB\'',
      'license' => 'GPLv3',
      'homepage'=>'https://github.com/pluginsGLPI/sccm',
      'requirements'   => [
         'glpi'   => [
            'min' => PLUGIN_SCCM_MIN_GLPI,
            'max' => PLUGIN_SCCM_MAX_GLPI,
            'dev' => true
         ],
         'php'    => [
            'min' => '7.0',
            'exts'=> [
               'sqlsrv'    => [
                  'required'  => true,
                  'function'  => 'sqlsrv_connect'
               ],
               'curl'      => [
                  'required'  => true,
                  'function'  => 'curl_init'
               ]
            ]
         ]
      ]
   ];
}



/**
 * Check pre-requisites before install
 *
 * @return boolean
 */
function plugin_sccm_check_prerequisites() {
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_sccm_check_config($verbose = false) {
   return true;
}
