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

class PluginSccmConfig extends CommonDBTM {

   static private $_instance = null;
   public $dohistory = true;

   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }

   static function canUpdate() {
      return Session::haveRight('config', UPDATE);
   }

   static function canView() {
      return Session::haveRight('config', UPDATE);
   }

   static function canPurge() {
      return Session::haveRight('config', UPDATE);
   }

   static function getTypeName($nb = 0) {
      return __("SCCM", "sccm");
   }

   function getName($with_comment = 0) {
      return __("Configuration", "sccm");
   }

   static function getInstance() {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
      }
      return self::$_instance;
   }

   public function rawSearchOptions()
   {
      $tab = [];
      $tab[] = [
         'id'                 => 'common',
         'name'               => self::getTypeName(2)
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'sccm_config_name',
         'name'               => __('Config name'),
         'massiveaction'      => false,
         'datatype'           => 'itemlink'
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'active_sync',
         'name'               => __('Enabled'),
         'massiveaction'      => false,
         'datatype'           => 'bool'
      ];
      $tab[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'sccm_collection_name',
         'name'               => __('Collection'),
         'massiveaction'      => false,
         'datatype'           => 'string'
      ];
      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'sccmdb_host',
         'name'               => __('Db Host'),
         'massiveaction'      => false,
         'datatype'           => 'string'
      ];
      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'sccmdb_dbname',
         'name'               => __('DB Name'),
         'massiveaction'      => false,
         'datatype'           => 'string'
      ];
      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'sccmdb_user',
         'name'               => __('DB User'),
         'massiveaction'      => false,
         'datatype'           => 'string'
      ];
      return $tab;
   }

   function getAllConfigurations() {
      return getAllDataFromTable(self::getTable());
   }

   public function prepareInputForUpdate($input) {
      return self::handleInput($input);
   }

   public function prepareInputForAdd($input) {
      return self::handleInput($input);
   }

   public static  function handleInput($input) {
      if (isset($input["sccmdb_password"]) AND !empty($input["sccmdb_password"])) {
         $input["sccmdb_password"] = (new GLPIKey())->encrypt($input["sccmdb_password"]);
      }

      if (array_key_exists('inventory_server_url', $input) && !empty($input['inventory_server_url'])) {
          $input['inventory_server_url'] = trim($input['inventory_server_url'], '/ ');
      }

      return $input;
   }

   static function isIdAutoIncrement()
   {
      global $DB;
      $columns = $DB->query("SHOW COLUMNS FROM glpi_plugin_sccm_configs WHERE FIELD = 'id'");
      $data = $columns->fetch_assoc();
      return str_contains($data["Extra"], "auto_increment");
   }

   static function install(Migration $migration) {
      global $CFG_GLPI, $DB;

      $default_charset = DBConnection::getDefaultCharset();
      $default_collation = DBConnection::getDefaultCollation();
      $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

      $table = self::getTable();

      if (!$DB->tableExists($table)) {

         $migration->displayMessage("Installing SCCM plugin ...");
         $migration->displayMessage("Table not exists, creating ...");

         $query = "CREATE TABLE `". $table."`(
                     `id` int {$default_key_sign} NOT NULL AUTO_INCREMENT,
                     `sccm_config_name` VARCHAR(255) NULL,
                     `sccmdb_host` VARCHAR(255) NULL,
                     `sccmdb_dbname` VARCHAR(255) NULL,
                     `sccmdb_user` VARCHAR(255) NULL,
                     `sccmdb_password` VARCHAR(255) NULL,
                     `sccm_collection_name` VARCHAR(255) NULL,
                     `inventory_server_url` VARCHAR(255) NULL,
                     `active_sync` tinyint NOT NULL default '0',
                     `verify_ssl_cert` tinyint NOT NULL default '0',
                     `use_auth_ntlm` tinyint NOT NULL default '0',
                     `unrestricted_auth` tinyint NOT NULL default '0',
                     `use_auth_info` tinyint NOT NULL default '0',
                     `auth_info` VARCHAR(255) NULL,
                     `is_password_sodium_encrypted` tinyint NOT NULL default '1',
                     `use_lasthwscan` tinyint NOT NULL default '0',
                     `date_mod` timestamp NULL default NULL,
                     `comment` text,
                     PRIMARY KEY  (`id`)
                   ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

         $DB->queryOrDie($query, $DB->error());

         // Update display preferences
         $migration->updateDisplayPrefs([PluginSccmConfig::class => [1, 2, 3, 4, 5, 6]]);

      } else {

         $migration->displayMessage("Updating SCCM plugin ...");

         // Need to move ID column to auto increment.
         if (!self::isIdAutoIncrement()) {
            $migration->changeField("glpi_plugin_sccm_configs", "id", "id", "autoincrement");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
            // Update display preferences
            $migration->updateDisplayPrefs([PluginSccmConfig::class => [1, 2, 3, 4, 5, 6]]);
         }

         if (!$DB->fieldExists($table, 'sccm_config_name')) {
            $migration->addField("glpi_plugin_sccm_configs", "sccm_config_name", "VARCHAR(255)");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'sccm_collection_name')) {
            $migration->addField("glpi_plugin_sccm_configs", "sccm_collection_name", "VARCHAR(255)");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'verify_ssl_cert')) {
            $migration->addField("glpi_plugin_sccm_configs", "verify_ssl_cert", "tinyint NOT NULL default '0'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'use_auth_ntlm')) {
            $migration->addField("glpi_plugin_sccm_configs", "use_auth_ntlm", "tinyint NOT NULL default '0'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'unrestricted_auth')) {
            $migration->addField("glpi_plugin_sccm_configs", "unrestricted_auth", "tinyint NOT NULL default '0'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'use_auth_info')) {
            $migration->addField("glpi_plugin_sccm_configs", "use_auth_info", "tinyint NOT NULL default '0'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'auth_info')) {
            $migration->addField("glpi_plugin_sccm_configs", "auth_info", "varchar(255)");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'is_password_sodium_encrypted')) {
            $config = self::getInstance();
            $configurations = $config->getAllConfigurations();
            foreach ($configurations as $data) {
               $config->getFromDB($data['id']);
               if (!empty($config->fields['sccmdb_password'])) {
                  $key = new GLPIKey();
                  $migration->addPostQuery(
                     $DB->buildUpdate(
                        'glpi_plugin_sccm_configs',
                        [
                           'sccmdb_password' => $key->encrypt(
                              $key->decryptUsingLegacyKey(
                                 $config->fields['sccmdb_password']
                              )
                           )
                        ],
                        [
                           'id' => $data['id'],
                        ]
                        )
                     );
               }
            }
            $migration->addField("glpi_plugin_sccm_configs", "is_password_sodium_encrypted", "tinyint NOT NULL default '1'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'use_lasthwscan')) {
            $migration->addField("glpi_plugin_sccm_configs", "use_lasthwscan", "tinyint NOT NULL default '0'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'fusioninventory_url')) {
            $migration->changeField("glpi_plugin_sccm_configs", "fusioninventory_url", "inventory_server_url", "string");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         $sccm_config = $DB->request(['FROM' => 'glpi_plugin_sccm_configs'])->current();
         $inventory_server_url = trim($sccm_config['inventory_server_url'] ?? '');
         $url_matches = [];
         if (
            $inventory_server_url !== ''
            && (
                preg_match('/^(?<base_url>.+)\/front\/inventory\.php$/', $inventory_server_url, $url_matches) === 1
                || preg_match('/^(?<base_url>.+)\/(marketplace|plugins)\/(fusioninventory)\//', $inventory_server_url, $url_matches) === 1
            )
         ){
            // Strip script path from base URL.
            $inventory_server_url = $url_matches['base_url'];
            if ($inventory_server_url === $CFG_GLPI['url_base']) {
                $inventory_server_url = '';
            }
            $sccm_config = $DB->update(
               'glpi_plugin_sccm_configs',
               [
                  'inventory_server_url' => $inventory_server_url,
               ],
               [
                  'id' => 1,
               ]
            );
         }

      }

      return true;
   }

   static function uninstall() {
      /** @var \DBmysql $DB */
      global $DB;
      $table = self::getTable();
      if ($DB->tableExists($table)) {
         $DB->queryOrDie("DROP TABLE IF EXISTS `" . self::getTable() . "`") or die($DB->error());
         $displayPref = new DisplayPreference();
         foreach ($displayPref->find(['itemtype' => PluginSccmConfig::class]) as $pref) {
               $displayPref->delete($pref);
         }
      }
   }

   static function searchUrl() {
      global $CFG_GLPI;
      return $CFG_GLPI['url_base'] . "/plugins/sccm/front/config.php";;
   }

   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }

   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      if (!$this->getFromDB($ID)) {
         $this->getEmpty();
      }

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("SCCM configuration name", "sccm")." (Id: ".$this->getField('id').")</td><td>";
      echo Html::input('sccm_config_name', ['value' => $this->getField('sccm_config_name')]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Enable SCCM synchronization", "sccm")."</td><td>";
      Dropdown::showYesNo("active_sync", $this->getField('active_sync'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Server hostname (MSSQL)", "sccm")."</td><td>";
      echo Html::input('sccmdb_host', ['value' => $this->getField('sccmdb_host')]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Database name", "sccm")."</td><td>";
      echo Html::input('sccmdb_dbname', ['value' => $this->getField('sccmdb_dbname')]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Username", "sccm")."</td><td>";
      echo Html::input('sccmdb_user', ['value' => $this->getField('sccmdb_user')]);
      echo "</td></tr>\n";

      $password = $this->getField('sccmdb_password');
      $password = Html::entities_deep((new GLPIKey())->decrypt($password));
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Password", "sccm")."</td><td>";
      echo "<input type='password' name='sccmdb_password' value='$password' autocomplete='off'>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("SCCM collection name", "sccm")."</td><td>";
      echo Html::input('sccm_collection_name', ['value' => $this->getField('sccm_collection_name')]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Inventory server base URL", "sccm")."</td><td>";
      echo Html::input(
         'inventory_server_url',
         [
            'type' => 'url',
            'pattern' => 'https?://.+',
            'value' => $this->getField('inventory_server_url'),
            'placeholder' => $CFG_GLPI['url_base'],
         ]
      );
      $url = ($this->getField('inventory_server_url') ?: $CFG_GLPI['url_base']) . '/front/inventory.php';
      echo '<span class="text-danger">' . $url . '</span>';
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Verify SSL certificate", "sccm")."</td><td>";
      Dropdown::showYesNo("verify_ssl_cert", $this->getField('verify_ssl_cert'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use NLTM authentication", "sccm")."</td><td>";
      Dropdown::showYesNo("use_auth_ntlm", $this->getField('use_auth_ntlm'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Send credentials to other hosts too", "sccm")."</td><td>";
      Dropdown::showYesNo("unrestricted_auth", $this->getField('unrestricted_auth'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use specific authentication information", "sccm")."</td><td>";
      Dropdown::showYesNo("use_auth_info", $this->getField('use_auth_info'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Value for spécific authentication", "sccm")."</td><td>";
      echo Html::input('auth_info', ['value' => $this->getField('auth_info')]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use LastHWScan as GLPI last inventory date", "sccm")."</td><td>";
      Dropdown::showYesNo("use_lasthwscan", $this->getField('use_lasthwscan'));
      echo "</td></tr>\n";

      $this->showFormButtons($options);
      return false;
   }

   public static function getIcon() {
      return "fa-solid fa-dice-d20";
   }
}
