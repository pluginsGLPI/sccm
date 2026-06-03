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
use Safe\Exceptions\SimplexmlException;
use Glpi\Exception\Http\BadRequestHttpException;

use function Safe\curl_exec;
use function Safe\curl_getinfo;
use function Safe\curl_init;
use function Safe\curl_setopt;
use function Safe\ini_set;
use function Safe\realpath;
use function Safe\simplexml_load_file;
use function Safe\sqlsrv_fetch_array;
use function Safe\mkdir;
use function Safe\preg_replace;

class PluginSccmSccm
{
    public array $devices;

    public function __construct()
    {
        if (!function_exists('curl_init')) {
            throw new BadRequestHttpException(
                __s('cURL extension (PHP) is required... !!', 'sccm'),
            );
        }

        if (!function_exists('sqlsrv_connect')) {
            throw new BadRequestHttpException(
                __s('SQLSRV extension (PHP) is required... !!', 'sccm'),
            );
        }
    }

    public static function getTypeName($nb = 0)
    {
        return __s('SCCM', 'sccm');
    }

    public function getDevices(int $config_id, $where = 0, $limit = 99999999): void
    {
        $sccm_db = new PluginSccmSccmdb();
        $res = $sccm_db->connect($config_id);
        if (!$res) {
            throw new BadRequestHttpException(
                __s('Cannot connect to SCCM database', 'sccm'),
            );
        }

        $config = new PluginSccmConfig();
        $config->getFromDB($config_id);

        $collection_name = (string) ($config->getField('sccm_collection_name') ?? '');

        $query = self::getcomputerQuery($collection_name);

        if ($where != 0) {
            $query .= " WHERE csd.MachineID = '" . $where . "'";
        }

        $result = $sccm_db->exec_query($query);

        $i = 0;
        $this->devices = [];

        while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) && $i < $limit) {
            $tab = $this->sanitizeRow($tab);
            $tab['MD-SystemName'] = strtoupper((string) $tab['MD-SystemName']);
            $this->devices[] = $tab;
            $i++;
        }

        $sccm_db->disconnect();
    }

    public function getDatas(PluginSccmSccmdb $sccm_db, $type, $deviceid, $limit = 99999999): array
    {
        if ($type == 'processors') {
            $fields = ['Manufacturer00', 'Name00', 'NormSpeed00', 'AddressWidth00', 'CPUKey00', 'NumberOfCores00', 'NumberOfLogicalProcessors00'];
            $table = 'Processor_DATA';
        } else {
            return [];
        }

        $query  = "SELECT " . implode(',', $fields) . "\n";
        $query .= " FROM " . $table . "\n";
        $query .= " WHERE MachineID = '" . $deviceid . "'" . "\n";

        $result = $sccm_db->exec_query($query);

        $data = [];
        $i    = 0;
        while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) && $i < $limit) {
            $data[] = $this->sanitizeRow($tab);
            $i++;
        }

        return $data;
    }

    public function getNetwork(PluginSccmSccmdb $sccm_db, $deviceid, $limit = 99999999): array
    {
        $query = "SELECT NeDa.IPAddress00 as \"ND-IpAddress\",
      NeDa.MACAddress00 as \"ND-MacAddress\",
      NeDa.IPSubnet00 as \"ND-IpSubnet\",
      NeDa.DefaultIPGateway00 as \"ND-IpGateway\",
      NeDa.DHCPServer00 as \"ND-DHCPServer\",
      NeDa.DNSDomain00 as \"ND-DomainName\",
      net.Name0 as \"ND-Name\"
      FROM Network_DATA NeDa
      INNER JOIN v_R_System VrS ON VrS.ResourceID=NeDa.MachineID
      INNER JOIN v_GS_NETWORK_ADAPTER net ON net.ResourceID=NeDa.MachineID AND NeDa.ServiceName00=net.ServiceName0
      WHERE MACAddress00 is not null
      AND NeDa.MachineID = '" . $deviceid . "'";

        $result = $sccm_db->exec_query($query);

        $data = [];
        $i    = 0;
        while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) && $i < $limit) {
            $data[] = $this->sanitizeRow($tab);
            $i++;
        }

        return $data;
    }

    public function getSoftware(PluginSccmSccmdb $sccm_db, $deviceid, $limit = 99999999): array
    {
        $query = "SELECT ArPd_64.DisplayName0 as \"ArPd-DisplayName\",
      ArPd_64.InstallDate0 as \"ArPd-InstallDate\",
      ArPd_64.Version0 as \"ArPd-Version\",
      ArPd_64.Publisher0 as \"ArPd-Publisher\"
      FROM v_GS_ADD_REMOVE_PROGRAMS_64 ArPd_64
      INNER JOIN v_R_System VrS on VrS.ResourceID=ArPd_64.ResourceID
      WHERE ArPd_64.ResourceID = {$deviceid}
      AND (ArPd_64.DisplayName0 is not null and ArPd_64.DisplayName0 <> '')
      UNION
      SELECT ArPd.DisplayName0 as \"ArPd-DisplayName\",
      ArPd.InstallDate0 as \"ArPd-InstallDate\",
      ArPd.Version0 as \"ArPd-Version\",
      ArPd.Publisher0 as \"ArPd-Publisher\"
      FROM v_GS_ADD_REMOVE_PROGRAMS ArPd
      INNER JOIN v_R_System VrS on VrS.ResourceID=ArPd.ResourceID
      WHERE ArPd.ResourceID = {$deviceid}
      AND (ArPd.DisplayName0 is not null and ArPd.DisplayName0 <> '')";

        $result = $sccm_db->exec_query($query);

        $data = [];
        $i    = 0;
        while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) && $i < $limit) {
            $data[] = $this->sanitizeRow($tab);
            $i++;
        }

        return $data;
    }

    public function getMemories(PluginSccmSccmdb $sccm_db, $deviceid, $limit = 99999999): array
    {
        $query = "SELECT
            Capacity0 as \"Mem-Capacity\",
            Caption0 as \"Mem-Caption\",
            Description0 as \"Mem-Description\",
            FormFactor0 as \"Mem-FormFactor\",
            Manufacturer0 as \"Mem-Manufacturer\",
            Removable0 as \"Mem-Removable\",
            '' as \"Mem-Purpose\",
            Speed0 as \"Mem-Speed\",
            BankLabel0 as \"Mem-Type\",
            GroupID as \"Mem-NumSlots\",
            '' as \"Mem-SerialNumber\"
         FROM v_GS_PHYSICAL_MEMORY
         WHERE ResourceID = '" . $deviceid . "'
         ORDER BY \"Mem-NumSlots\"";

        $result = $sccm_db->exec_query($query);

        $data = [];
        $i    = 0;
        while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) && $i < $limit) {
            $data[] = $this->sanitizeRow($tab);
            $i++;
        }

        return $data;
    }

    public function getVideos(PluginSccmSccmdb $sccm_db, $deviceid, $limit = 99999999): array
    {
        $query = "
      SELECT
         VideoProcessor0 as \"Vid-Chipset\",
         AdapterRAM0/1024 as \"Vid-Memory\",
         Name0 as \"Vid-Name\",
         CONCAT(CurrentHorizontalResolution0, 'x', CurrentVerticalResolution0) as \"Vid-Resolution\",
         GroupID as \"Vid-PciSlot\"
      FROM v_GS_VIDEO_CONTROLLER
      WHERE VideoProcessor0 is not null
      AND ResourceID = '" . $deviceid . "'
      ORDER BY GroupID";

        $result = $sccm_db->exec_query($query);

        $data = [];
        $i    = 0;
        while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) && $i < $limit) {
            $data[] = $this->sanitizeRow($tab);
            $i++;
        }

        return $data;
    }

    public function getSounds(PluginSccmSccmdb $sccm_db, $deviceid, $limit = 99999999): array
    {
        $query = "
      SELECT distinct
         Description0 as \"Snd-Description\",
         Manufacturer0 as \"Snd-Manufacturer\",
         Name0 as \"Snd-Name\"
      FROM v_GS_SOUND_DEVICE
      WHERE ResourceID = '" . $deviceid . "'";

        $result = $sccm_db->exec_query($query);

        $data = [];
        $i    = 0;
        while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) && $i < $limit) {
            $data[] = $this->sanitizeRow($tab);
            $i++;
        }

        return $data;
    }

    public function getStorages(PluginSccmSccmdb $sccm_db, $deviceid, $limit = 99999999): array
    {
        $query = "
      SELECT
         md.SystemName00,
         gld.ResourceID as \"gld-ResourceID\",
         gld.Description0 as \"gld-Description\",
         gld.DeviceID0 as \"gld-Partition\",
         gld.FileSystem0 as \"gld-FileSystem\",
         gld.Size0 as \"gld-TotalSize\",
         gld.FreeSpace0 as \"gld-FreeSpace\",
         gld.VolumeName0 as \"gld-MountingPoint\",
         gdi.Caption0 as \"gdi-Caption\"
      FROM v_GS_LOGICAL_DISK as gld
      INNER JOIN v_gs_Disk as gdi on gdi.ResourceID = gld.ResourceID
      LEFT JOIN Motherboard_DATA as md on gld.ResourceID = md.MachineID
      WHERE gld.GroupID = gdi.GroupID
      AND gld.ResourceID = '" . $deviceid . "'";

        $result = $sccm_db->exec_query($query);

        $data = [];
        $i    = 0;
        while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) && $i < $limit) {
            $data[] = $this->sanitizeRow($tab);
            $i++;
        }

        return $data;
    }

    public function getMedias(PluginSccmSccmdb $sccm_db, $deviceid, $limit = 99999999): array
    {
        $query = "
      SELECT distinct
         Description0 as \"Med-Description\",
         Manufacturer0 as \"Med-Manufacturer\",
         Caption0 as \"Med-Model\",
         Name0 as \"Med-Name\",
         SCSITargetID0 as \"Med-SCSITargetId\",
         MediaType0 as \"Med-Type\"
      FROM v_GS_CDROM
      WHERE ResourceID = '" . $deviceid . "'";

        $result = $sccm_db->exec_query($query);

        $data = [];
        $i    = 0;
        while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) && $i < $limit) {
            $data[] = $this->sanitizeRow($tab);
            $i++;
        }

        return $data;
    }

    private function sanitizeRow(array $row): array
    {
        return array_map(static function ($v) {
            if (!is_string($v)) {
                return $v;
            }

            if (($pos = strpos($v, "\0")) !== false) {
                $v = substr($v, 0, $pos);
            }

            $v = mb_scrub($v, 'UTF-8');
            return preg_replace('/[\x{FFFE}\x{FFFF}]/u', '', $v) ?? '';
        }, $row);
    }

    public static function install(): void
    {
        $cron = new CronTask();

        if ($cron->getFromDBbyName(self::class, 'sccm')) {
            $cron->fields["name"]    = "SCCMCollect";
            $cron->fields["hourmin"] = 4;
            $cron->fields["hourmax"] = 5;
            $cron->update($cron->fields);
        } elseif (!$cron->getFromDBbyName(self::class, 'SCCMCollect')) {
            CronTask::register(
                self::class,
                'SCCMCollect',
                7 * DAY_TIMESTAMP,
                ['param' => 24, 'mode' => CronTask::MODE_EXTERNAL, 'hourmin' => 4, 'hourmax' => 5],
            );
        }

        if (!$cron->getFromDBbyName(self::class, 'SCCMPush')) {
            CronTask::register(
                self::class,
                'SCCMPush',
                7 * DAY_TIMESTAMP,
                ['param' => 24, 'mode' => CronTask::MODE_EXTERNAL, 'hourmin' => 6, 'hourmax' => 7],
            );
        }
    }

    public static function uninstall(): void
    {
        CronTask::unregister(self::class);
    }

    public static function cronSCCMCollect($task): int
    {
        return self::executeCollect($task);
    }

    public static function cronSCCMPush($task): int
    {
        return self::executePush($task);
    }

    public static function cronInfo($name): ?array
    {
        if ($name === "SCCMCollect") {
            return ['description' => __s("Interface - SCCMCollect", "sccm")];
        }

        if ($name === "SCCMPush") {
            return ['description' => __s("Interface - SCCMPush", "sccm")];
        }

        return null;
    }

    public static function executeCollect($task): int
    {
        ini_set('max_execution_time', '0');

        $active_configs = PluginSccmConfig::getAllActiveConfigs();

        if ($active_configs === []) {
            echo __s("Collect is disabled by configuration.", "sccm");
            return -1;
        }

        $retcode = -1;

        foreach (array_keys($active_configs) as $config_id) {
            $REP_XML = GLPI_PLUGIN_DOC_DIR . '/sccm/xml/' . $config_id . '/';
            if (!is_dir($REP_XML)) {
                mkdir($REP_XML, 0755, true);
            }

            try {
                $PluginSccmSccm = new PluginSccmSccm();
                $PluginSccmSccm->getDevices($config_id);

                Toolbox::logInFile(
                    'sccm',
                    sprintf('[Config %s] getDevices OK - ', $config_id) . count($PluginSccmSccm->devices) . " files\n",
                    true,
                );

                $sccm_db = new PluginSccmSccmdb();
                if (!$sccm_db->connect($config_id)) {
                    throw new RuntimeException(
                        sprintf('[Config %s] Cannot connect to SCCM database', $config_id),
                    );
                }

                foreach ($PluginSccmSccm->devices as $device_values) {
                    $PluginSccmSccmxml = new PluginSccmSccmxml($device_values);

                    $PluginSccmSccmxml->setAccessLog();
                    $PluginSccmSccmxml->setAccountInfos();
                    $PluginSccmSccmxml->setHardware();
                    $PluginSccmSccmxml->setOS();
                    $PluginSccmSccmxml->setBios();
                    $PluginSccmSccmxml->setProcessors($sccm_db);
                    $PluginSccmSccmxml->setSoftwares($sccm_db);
                    $PluginSccmSccmxml->setMemories($sccm_db);
                    $PluginSccmSccmxml->setVideos($sccm_db);
                    $PluginSccmSccmxml->setSounds($sccm_db);
                    $PluginSccmSccmxml->setUsers();
                    $PluginSccmSccmxml->setNetworks($sccm_db);
                    $PluginSccmSccmxml->setStorages($sccm_db);

                    $SXML = $PluginSccmSccmxml->sxml;
                    $SXML->asXML($REP_XML . $PluginSccmSccmxml->device_id . ".ocs");

                    Toolbox::logInFile('sccm', sprintf('[Config %s] Collect OK for device - ', $config_id) . $PluginSccmSccmxml->device_id . "\n", true);
                    $task->addVolume(1);
                }

                $sccm_db->disconnect();
                Toolbox::logInFile('sccm', "[Config {$config_id}] Collect completed\n", true);
                $retcode = 1;
            } catch (Throwable $e) {
                Toolbox::logInFile('sccm', sprintf('[Config %s] Collect ERROR: ', $config_id) . $e->getMessage() . "\n", true);
            }
        }

        return $retcode;
    }

    public static function getcomputerQuery(string $collection_name = ''): string
    {
        $query = "SELECT csd.Description00 as \"CSD-Description\",
      csd.Domain00 as \"CSD-Domain\",
      csd.Manufacturer00 as \"CSD-Manufacturer\",
      csd.Model00 as \"CSD-Model\",
      csd.Roles00 as \"CSD-Roles\",
      csd.SystemType00 as \"CSD-SystemType\",
      csd.UserName00 as \"CSD-UserName\",
      csd.MachineID as \"CSD-MachineID\",
      csd.TimeKey as \"CSD-TimeKey\",
      md.SystemName00 as \"MD-SystemName\",
      osd.BuildNumber00 as \"OSD-BuildNumber\",
      osd.Caption00 as \"OSD-Caption\",
      osd.CSDVersion00 as \"OSD-CSDVersion\",
      osd.BootDevice00 as \"OSD-BootDevice\",
      osd.InstallDate00 as \"OSD-InstallDate\",
      osd.LastBootUpTime00 as \"OSD-LastBootUpTime\",
      osd.Manufacturer00 as \"OSD-Manufacturer\",
      osd.Name00 as \"OSD-Name\",
      osd.Organization00 as \"OSD-Organization\",
      osd.RegisteredUser00 as \"OSD-RegisteredUser\",
      osd.TotalVirtualMemorySize00 as \"OSD-TotalVirtualMemory\",
      osd.TotalVisibleMemorySize00 as \"OSD-TotalVisibleMemory\",
      osd.Version00 as \"OSD-Version\",
      pbd.SerialNumber00 as \"PBD-SerialNumber\",
      pbd.ReleaseDate00 as \"PBD-ReleaseDate\",
      pbd.Name00 as \"PBD-Name\",
      pbd.SMBIOSBIOSVersion00 as \"PBD-BiosVersion\",
      pbd.Version00 as \"PBD-Version\",
      pbd.Manufacturer00 as \"PBD-Manufacturer\",
      sdi.User_Name0 as \"SDI-UserName\",
      sd.SMSID0 as \"SD-UUID\",
      sd.SystemRole0 as \"SD-SystemRole\",
      VrS.User_Name0 as \"VrS-UserName\",
      vWD.LastHWScan as \"vWD-LastScan\"
      FROM Computer_System_DATA csd
      LEFT JOIN Motherboard_DATA md ON csd.MachineID = md.MachineID
      LEFT JOIN Operating_System_DATA osd ON csd.MachineID = osd.MachineID
      LEFT JOIN v_GS_WORKSTATION_STATUS vWD ON csd.MachineID = vWD.ResourceID
      LEFT JOIN PC_BIOS_DATA pbd ON csd.MachineID = pbd.MachineID
      LEFT JOIN System_DISC sdi ON csd.MachineID = sdi.ItemKey
      LEFT JOIN System_DATA sd ON csd.MachineID = sd.MachineID
      INNER JOIN v_R_System VrS ON csd.MachineID = VrS.ResourceID
      WHERE csd.MachineID is not null and csd.MachineID != ''";

        if ($collection_name !== '') {
            $safe_name = str_replace("'", "''", $collection_name);
            $query .= " AND csd.MachineID IN (
      SELECT fcm.ResourceID
      FROM v_FullCollectionMembership fcm
      INNER JOIN v_Collection vc ON fcm.CollectionID = vc.CollectionID
      WHERE vc.Name = N'{$safe_name}'
    )";
        }

        return $query;
    }

    public static function executePush(CronTask $task): int
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $active_configs = PluginSccmConfig::getAllActiveConfigs();

        if ($active_configs === []) {
            if (isCommandLine()) {
                echo "Push is disabled by configuration.\n";
            } else {
                Session::addMessageAfterRedirect(__s("Push is disabled by configuration.", "sccm"));
            }

            return -1;
        }

        $retcode = -1;

        foreach (array_keys($active_configs) as $config_id) {
            $PluginSccmSccmdb = new PluginSccmSccmdb();
            $res = $PluginSccmSccmdb->connect($config_id);

            if (!$res) {
                Toolbox::logInFile('sccm', "[Config {$config_id}] Cannot connect to SCCM database\n", true);

                if (isCommandLine()) {
                    echo "[Config {$config_id}] Cannot connect to SCCM database\n";
                } else {
                    Session::addMessageAfterRedirect(__s("[Config {$config_id}] Cannot connect to SCCM database\n"));
                }

                continue;
            }

            $config = new PluginSccmConfig();
            $config->getFromDB($config_id);
            $collection_name = (string) ($config->getField('sccm_collection_name') ?? '');

            $query  = self::getcomputerQuery($collection_name);
            $result = $PluginSccmSccmdb->exec_query($query);

            while ($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $REP_XML = realpath(GLPI_PLUGIN_DOC_DIR . '/sccm/xml/' . $config_id . '/' . $tab['CSD-MachineID'] . '.ocs');

                if ($REP_XML === '0') {
                    Toolbox::logInFile('sccm', sprintf('[Config %s] Path not found for device ', $config_id) . $tab['CSD-MachineID'] . "\n", true);
                    continue;
                }

                try {
                    $xmlFile = simplexml_load_file($REP_XML, 'SimpleXMLElement', LIBXML_NOCDATA);
                } catch (SimplexmlException $e) {
                    Toolbox::logInFile('sccm', sprintf("[Config %s] Can't load file: %s%s%s%s", $config_id, $REP_XML, PHP_EOL, $e->getMessage(), PHP_EOL), true);
                    continue;
                }


                $ch = curl_init();
                if ($config->getField('verify_ssl_cert') != "1") {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                }

                if ($config->getField('use_auth_ntlm') == "1") {
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
                }

                if ($config->getField('unrestricted_auth') == "1") {
                    curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
                }

                if ($config->getField('use_auth_info') == "1") {
                    curl_setopt($ch, CURLOPT_USERPWD, $config->getField('auth_info'));
                }

                $url = ($config->getField('inventory_server_url') ?: $CFG_GLPI['url_base']) . '/front/inventory.php';

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlFile->asXML());
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_REFERER, $CFG_GLPI['url_base']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $ch_result = curl_exec($ch);
                if ($ch_result === false) {
                    Toolbox::logInFile('sccm', sprintf('[Config %s] cURL error: ', $config_id) . curl_error($ch) . "\n", true);
                } else {
                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if ($httpcode != 200) {
                        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                        $body        = substr($ch_result, $header_size);
                        Toolbox::logInFile('sccm', sprintf('[Config %s] Push KO - ', $config_id) . $tab['CSD-MachineID'] . sprintf(' -> STATUS: %s%s%s%s', $httpcode, PHP_EOL, $body, PHP_EOL), true);
                    } else {
                        $task->addVolume(1);

                        if ($config->getField('use_lasthwscan') == 1) {
                            $agent = new Agent();
                            if (
                                $agent->getFromDBByCrit(["name" => $tab['CSD-MachineID']])
                                && class_exists($agent->fields['itemtype'])
                                && is_a($agent->fields['itemtype'], CommonDBTM::class, true)
                            ) {
                                $asset = new $agent->fields['itemtype']();
                                if ($asset->getFromDB($agent->fields['items_id'])) {
                                    $asset->update([
                                        "id"                   => $asset->fields['id'],
                                        "last_inventory_update" => $tab['vWD-LastScan']->format('Y-m-d h:i'),
                                    ]);
                                }
                            }
                        }

                        Toolbox::logInFile('sccm', sprintf('[Config %s] Push OK - ', $config_id) . $tab['CSD-MachineID'] . "\n", true);
                    }

                    curl_close($ch);
                }

                curl_close($ch);
            }

            Toolbox::logInFile('sccm', "[Config {$config_id}] Push completed\n", true);
            $PluginSccmSccmdb->disconnect();
            $task->addVolume(1);
            $retcode = 1;
        }

        return $retcode;
    }
}
