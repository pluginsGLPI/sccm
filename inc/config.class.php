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

use function Safe\preg_match;

class PluginSccmConfig extends CommonDBTM
{
    public static $rightname = 'config';

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

    public static function canPurge(): bool
    {
        return Session::haveRight('config', UPDATE);
    }

    public static function getTypeName($nb = 0): string
    {
        return _n('SCCM Configuration', 'SCCM Configurations', $nb, 'sccm');
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    public static function getAllActiveConfigs(): array
    {
        return (new self())->find(['active_sync' => 1]);
    }

    public function rawSearchOptions(): array
    {
        $options = parent::rawSearchOptions();

        $options[] = [
            'id'            => 3,
            'table'         => $this->getTable(),
            'field'         => 'active_sync',
            'name'          => __('Enable synchronization', 'sccm'),
            'datatype'      => 'bool',
        ];

        $options[] = [
            'id'            => 4,
            'table'         => $this->getTable(),
            'field'         => 'sccmdb_host',
            'name'          => __('Server hostname (MSSQL)', 'sccm'),
            'datatype'      => 'string',
        ];

        $options[] = [
            'id'            => 5,
            'table'         => $this->getTable(),
            'field'         => 'sccmdb_dbname',
            'name'          => __('Database name', 'sccm'),
            'datatype'      => 'string',
        ];

        $options[] = [
            'id'            => 6,
            'table'         => $this->getTable(),
            'field'         => 'date_mod',
            'name'          => __('Last update'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
        ];

        $options[] = [
            'id'            => 7,
            'table'         => $this->getTable(),
            'field'         => 'date_creation',
            'name'          => __('Creation date'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
        ];

        return $options;
    }

    public function prepareInputForAdd($input): array
    {
        return $this->prepareInputForUpdate($input);
    }

    public function prepareInputForUpdate($input): array
    {
        if (isset($input["sccmdb_password"]) && !empty($input["sccmdb_password"])) {
            $input["sccmdb_password"] = (new GLPIKey())->encrypt($input["sccmdb_password"]);
        } else {
            unset($input["sccmdb_password"]);
        }

        if (array_key_exists('inventory_server_url', $input) && !empty($input['inventory_server_url'])) {
            $input['inventory_server_url'] = trim((string) $input['inventory_server_url'], '/ ');
        }

        return $input;
    }

    public function showForm($ID, array $options = []): bool
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $this->initForm($ID, $options);

        $password = (new GLPIKey())->decrypt($this->fields['sccmdb_password'] ?? '');
        $url = ($this->fields['inventory_server_url'] ?: __('Example:') . ' ' . $CFG_GLPI['url_base']) . '/front/inventory.php';

        TemplateRenderer::getInstance()->display(
            '@sccm/config.html.twig',
            [
                'item'             => $this,
                'params'           => $options,
                'password_display' => $password,
                'url'              => $url,
            ],
        );

        return true;
    }

    public static function isIdAutoIncrement(): bool
    {
        /** @var DBmysql $DB */
        global $DB;

        $field = $DB->getField('glpi_plugin_sccm_configs', 'id', false);
        if ($field !== null) {
            $extra = $field['Extra'] ?? $field['extra'] ?? '';
            return str_contains(strtolower($extra), 'auto_increment');
        }

        return false;
    }

    public static function install(Migration $migration): bool
    {
        /** @var array $CFG_GLPI */
        /** @var DBmysql $DB */
        global $CFG_GLPI, $DB;

        $default_charset   = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();

        $table = 'glpi_plugin_sccm_configs';

        if (!$DB->tableExists($table)) {

            $query = "CREATE TABLE `{$table}`(
                     `id`                         int {$default_key_sign} NOT NULL AUTO_INCREMENT,
                     `name`                       VARCHAR(255) NOT NULL DEFAULT '',
                     `sccmdb_host`                VARCHAR(255) NULL,
                     `sccmdb_dbname`              VARCHAR(255) NULL,
                     `sccmdb_user`                VARCHAR(255) NULL,
                     `sccmdb_password`            VARCHAR(255) NULL,
                     `inventory_server_url`       VARCHAR(255) NULL,
                     `active_sync`                tinyint NOT NULL DEFAULT '0',
                     `verify_ssl_cert`            tinyint NOT NULL DEFAULT '0',
                     `use_auth_ntlm`              tinyint NOT NULL DEFAULT '0',
                     `unrestricted_auth`          tinyint NOT NULL DEFAULT '0',
                     `use_auth_info`              tinyint NOT NULL DEFAULT '0',
                     `auth_info`                  VARCHAR(255) NULL,
                     `use_lasthwscan`             tinyint NOT NULL DEFAULT '0',
                     `date_mod`                   timestamp NULL DEFAULT NULL,
                     `date_creation`              timestamp NULL DEFAULT NULL,
                     `comment`                    text,
                     PRIMARY KEY (`id`),
                     KEY `name` (`name`)
                   ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

            $DB->doQuery($query);

        } else {

            // Make id auto-increment to support multiple configurations
            if (!self::isIdAutoIncrement()) {
                $migration->changeField($table, 'id', 'id', 'autoincrement');
                $migration->migrationOneTable($table);
            }

            // Add name field — migrate existing record as 'Default'
            if (!$DB->fieldExists($table, 'name')) {
                $migration->addField($table, 'name', 'string', ['after' => 'id', 'value' => '']);
                $migration->addPostQuery(
                    $DB->buildUpdate($table, ['name' => 'Default'], ['id' => 1]),
                );
                $migration->migrationOneTable($table);
                $migration->addKey($table, 'name', 'name');
            }

            if (!$DB->fieldExists($table, 'date_creation')) {
                $migration->addField($table, 'date_creation', 'timestamp', ['after' => 'date_mod']);
                $migration->migrationOneTable($table);
            }

            if (!$DB->fieldExists($table, 'verify_ssl_cert')) {
                $migration->addField($table, "verify_ssl_cert", "tinyint NOT NULL default '0'");
                $migration->migrationOneTable($table);
            }

            if (!$DB->fieldExists($table, 'use_auth_ntlm')) {
                $migration->addField($table, "use_auth_ntlm", "tinyint NOT NULL default '0'");
                $migration->migrationOneTable($table);
            }

            if (!$DB->fieldExists($table, 'unrestricted_auth')) {
                $migration->addField($table, "unrestricted_auth", "tinyint NOT NULL default '0'");
                $migration->migrationOneTable($table);
            }

            if (!$DB->fieldExists($table, 'use_auth_info')) {
                $migration->addField($table, "use_auth_info", "tinyint NOT NULL default '0'");
                $migration->migrationOneTable($table);
            }

            if (!$DB->fieldExists($table, 'auth_info')) {
                $migration->addField($table, "auth_info", "varchar(255)");
                $migration->migrationOneTable($table);
            }

            // Sodium encryption migration — iterate ALL configs
            if (!$DB->fieldExists($table, 'is_password_sodium_encrypted')) {
                $key = new GLPIKey();
                foreach ($DB->request(['FROM' => $table]) as $config_data) {
                    if (!empty($config_data['sccmdb_password'])) {
                        $migration->addPostQuery(
                            $DB->buildUpdate(
                                $table,
                                ['sccmdb_password' => $key->encrypt($key->decryptUsingLegacyKey($config_data['sccmdb_password']))],
                                ['id' => $config_data['id']],
                            ),
                        );
                    }
                }

                $migration->addField($table, "is_password_sodium_encrypted", "tinyint NOT NULL default '1'");
                $migration->migrationOneTable($table);
            }

            if (!$DB->fieldExists($table, 'use_lasthwscan')) {
                $migration->addField($table, "use_lasthwscan", "tinyint NOT NULL default '0'");
                $migration->migrationOneTable($table);
            }

            if (!$DB->fieldExists($table, 'fusioninventory_url')) {
                $migration->changeField($table, "fusioninventory_url", "inventory_server_url", "string");
                $migration->migrationOneTable($table);
            }

            // Strip old full inventory URL paths stored in existing configs
            foreach ($DB->request(['FROM' => $table]) as $sccm_config) {
                $inventory_server_url = trim($sccm_config['inventory_server_url'] ?? '');
                $url_matches = [];
                if (
                    $inventory_server_url !== ''
                    && (
                        preg_match('/^(?<base_url>.+)\/front\/inventory\.php$/', $inventory_server_url, $url_matches) === 1
                        || preg_match('/^(?<base_url>.+)\/(marketplace|plugins)\/(fusioninventory)\//', $inventory_server_url, $url_matches) === 1
                    )
                ) {
                    $inventory_server_url = $url_matches['base_url'];
                    if ($inventory_server_url === $CFG_GLPI['url_base']) {
                        $inventory_server_url = '';
                    }

                    $DB->update($table, ['inventory_server_url' => $inventory_server_url], ['id' => $sccm_config['id']]);
                }
            }
        }

        $migration->updateDisplayPrefs([self::class => [1, 4, 5]], [], true);

        return true;
    }

    public static function uninstall(): bool
    {
        /** @var DBmysql $DB */
        global $DB;

        if ($DB->tableExists('glpi_plugin_sccm_configs')) {
            $DB->doQuery("DROP TABLE `glpi_plugin_sccm_configs`");
        }

        $table = DisplayPreference::getTable();
        $DB->delete($table, ['itemtype' => self::class]);

        return true;
    }

    public static function getIcon()
    {
        return "ti ti-database-cog";
    }

}
