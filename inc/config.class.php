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

use Glpi\Application\View\TemplateRenderer;

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

class PluginSccmConfig extends CommonDBTM
{
    private static $_instance;
    public $dohistory = true;

    public static function canCreate(): bool
    {
        return Session::haveRight('config', UPDATE);
    }

    public static function canUpdate(): bool
    {
        return Session::haveRight('config', UPDATE);
    }

    public static function canView(): bool
    {
        return Session::haveRight('config', UPDATE);
    }

    public static function getTypeName($nb = 0)
    {
        return __s("SCCM", "sccm");
    }

    public function getName($options = [])
    {
        return __("Configuration", "sccm");
    }

    public static function getInstance()
    {
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

    public function getAllConfigurations() {
        return getAllDataFromTable(self::getTable());
    }

    public function loadFirstConfiguration() {
        $configurations = $this->getAllConfigurations();
        if (empty($configurations)) {
            return false;
        }
        $this->getFromDB(array_values($configurations)[0]['id']);
        
        return true;
    }

    public function prepareInputForUpdate($input)
    {
        if (isset($input["sccmdb_password"]) && !empty($input["sccmdb_password"])) {
            $input["sccmdb_password"] = (new GLPIKey())->encrypt($input["sccmdb_password"]);
        }
        if (array_key_exists('inventory_server_url', $input) && !empty($input['inventory_server_url'])) {
            $input['inventory_server_url'] = trim((string) $input['inventory_server_url'], '/ ');
        }

        return $input;
    }

    function prepareInputForAdd($input) {
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
        Toolbox::logInFile('sccm', "Auto increment ... " . $data["Extra"] . " \n", true);
        return str_contains($data["Extra"], "auto_increment");
    }


    public static function install(Migration $migration)
    {
        /** @var array $CFG_GLPI */
        /** @var DBmysql $DB */
        global $CFG_GLPI, $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = 'glpi_plugin_sccm_configs';
        Toolbox::logInFile('sccm', "Installing ...\n", true);


        if (!$DB->tableExists($table)) {

            Toolbox::logInFile('sccm', "Table not exists, creating ...\n", true);

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

            $DB->doQuery($query);

            $query = "INSERT INTO `$table`
                        (date_mod, sccmdb_host, sccmdb_dbname,
                            sccmdb_user, sccmdb_password, inventory_server_url)
                      VALUES (NOW(), 'srv_sccm','bdd_sccm','user_sccm','',
                        NULL)";

            $DB->doQuery($query);

        } else {
            if (!self::isIdAutoIncrement()){
                Toolbox::logInFile('sccm', "Changing to Auto increment ... \n", true);
                $migration->changeField("glpi_plugin_sccm_configs", "id", "id", "autoincrement");
                $migration->migrationOneTable('glpi_plugin_sccm_configs');            
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

            $config = self::getInstance();
            $configurations = $config->getAllConfigurations();
            foreach ($configurations as $sccm_config) {                
                $inventory_server_url = trim($sccm_config['inventory_server_url'] ?? '');
                $url_matches = [];
                if (
                    $inventory_server_url !== ''
                    && (
                        preg_match('/^(?<base_url>.+)\/front\/inventory\.php$/', $inventory_server_url, $url_matches) === 1
                        || preg_match('/^(?<base_url>.+)\/(marketplace|plugins)\/(fusioninventory)\//', $inventory_server_url, $url_matches) === 1
                    )
                ) {
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
        }

        return true;
    }

    public static function uninstall()
    {
        /** @var DBmysql $DB */
        global $DB;

        Toolbox::logInFile('sccm', "Uninstalling ...\n", true);
        if ($DB->tableExists('glpi_plugin_sccm_configs')) {

            $query = "DROP TABLE `glpi_plugin_sccm_configs`";
            $DB->doQuery($query);
        }

        return true;
    }

    public function getFormFields(): array
    {
        return [];
    }

    public function showForm($ID, array $options = [])
    {
        /**
         * @var array $CFG_GLPI
         */
        global $CFG_GLPI;

        $config = self::getInstance();
        $password = $config->getField('sccmdb_password');
        $password = (new GLPIKey())->decrypt($password);
        $config->fields['sccmdb_password'] = $password;

        $url = ($config->getField('inventory_server_url') ?: "Ex : " . $CFG_GLPI['url_base']) . '/front/inventory.php';

        TemplateRenderer::getInstance()->display(
            '@sccm/config.html.twig',
            [
                'action'    => Toolbox::getItemTypeFormURL(self::class),
                'item'      => $config,
                'url'       => $url,
            ],
        );

        return true;
    }
        
    public static function searchUrl() {      
        global $CFG_GLPI;
        return $CFG_GLPI['url_base'] . "/plugins/sccm/front/config.php";;
    }

    public static function showConfigList() {
        global $DB;

        $configUrl = self::configUrl();

        echo "<p>SCCM Configuration list: </p>";
        echo "<ul>";

        $configs = $DB->query("select * from glpi_plugin_sccm_configs");
        while ($data = $configs->fetch_assoc()) {
            echo "   <li> <a href='" . $configUrl . "?id=" . $data['id'] . "'>".$data['sccm_config_name']."</a>";
        }
        echo "   <li> <a href='" . $configUrl . "?id=-1'>Add new ...</a>";
        echo "</ul>";
    }

    public function defineTabs($options = []) {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab(__CLASS__, $ong, $options);
        $this->addStandardTab('Log', $ong, $options);
        return $ong;
    }


    public static function canPurge() {
        return Session::haveRight('config', UPDATE);
    }
}
