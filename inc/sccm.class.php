<?php
/*
 *
 -------------------------------------------------------------------------
 GLPISCCMPlugin
 Copyright (C) 2013 by teclib.

 http://www.teclib.com
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPISCCMPlugin.

 GLPISCCMPlugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPISCCMPlugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPISCCMPlugin. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Original Author of file: François Legastelois <flegastelois@teclib.com>
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginSccmSccm {

   var $devices;

   static function getTypeName($nb=0) {
      return __('SCCM', 'sccm');
   }

   function showHome() {
      echo __('Please, read the documentation before using that.','footprints');
   }

   function getDevices($where = 0, $limit = 99999999) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $PluginSccmSccmdb->connect();

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
      VrS.User_Name0 as \"VrS-UserName\"
      FROM Computer_System_DATA csd
      LEFT JOIN Motherboard_DATA md ON csd.MachineID = md.MachineID
      LEFT JOIN Operating_System_DATA osd ON csd.MachineID = osd.MachineID
      LEFT JOIN PC_BIOS_DATA pbd ON csd.MachineID = pbd.MachineID
      LEFT JOIN System_DISC sdi ON csd.MachineID = sdi.ItemKey
      LEFT JOIN System_DATA sd ON csd.MachineID = sd.MachineID
      INNER JOIN v_R_System VrS ON csd.MachineID = VrS.ResourceID 
      ";

      if($where!=0) {
         $query.= " WHERE csd.MachineID = '" . $where . "'";
      }

      $result = $PluginSccmSccmdb->exec_query($query);

      $i = 0;

      while($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC) AND $i < $limit) {

         $tab['MD-SystemName'] = strtoupper($tab['MD-SystemName']);

         $this->devices[] = $tab;

         $i++;
      }

      $PluginSccmSccmdb->disconnect();
   }

   function getDatas($type, $deviceid) {
      
      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $PluginSccmSccmdb->connect();

      $datas = array();
      
      switch($type){
         case 'drives':
            $fields = array('Caption00','Description00','DeviceID00','InterfaceType00',
                              'Manufacturer00','Model00','Name00','Size00');
            $table = 'Disk_DATA';
         break;
         case 'processors' :
            $fields = array('Manufacturer00','Name00','NormSpeed00','AddressWidth00','CPUKey00');
            $table = 'Processor_DATA';
         break;
      }
      
      $query = "SELECT ".implode(',',$fields)."\n";
      $query.= " FROM ".$table."\n";
      $query.= " WHERE MachineID = '".$deviceid."'"."\n";

      $result = $PluginSccmSccmdb->exec_query($query);
      while($data = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
         foreach($data as $key => $value){
            $data[$key] = $this->cleanValue($value);
         }
         $datas[]=$data;
      }

      $PluginSccmSccmdb->disconnect();

      return $datas;
   }

   function cleanValue($value) {
      $value = Html::clean($value);
      $value = Toolbox::clean_cross_side_scripting_deep($value);
      $value = Toolbox::addslashes_deep($value);
      return $value;
   }

   function getNetwork($deviceid) {
      
      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $PluginSccmSccmdb->connect();

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
      WHERE NeDa.IPEnabled00=1
      AND NeDa.MachineID = '".$deviceid."'";
      
      $datas = array();

      $result = $PluginSccmSccmdb->exec_query($query);
      while($data = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
         foreach($data as $key => $value){
            $data[$key] = $this->cleanValue($value);
         }
         $datas[]=$data;
      }

      $PluginSccmSccmdb->disconnect();

      return $datas;
   }

   function getSoftware($deviceid) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $PluginSccmSccmdb->connect();

      $query = "SELECT ArPd.DisplayName00 as \"ArPd-DisplayName\",
      ArPd.InstallDate00 as \"ArPd-InstallDate\",
      ArPd.Version00 as \"ArPd-Version\",
      ArPd.Publisher00 as \"ArPd-Publisher\"
      FROM Add_Remove_Programs_DATA ArPd
      INNER JOIN v_R_System VrS on VrS.ResourceID=ArPd.MachineID
      WHERE ArPd.MachineID = '".$deviceid."'";
      
      $datas = array();

      $result = $PluginSccmSccmdb->exec_query($query);
      while($data = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
         foreach($data as $key => $value){
            $data[$key] = utf8_encode($this->cleanValue($value));
         }
         $datas[]=$data;
      }

      $PluginSccmSccmdb->disconnect();

      return $datas;
   }

   static function install() {
      $cron = new CronTask;
      if (!$cron->getFromDBbyName(__CLASS__, 'sccm')) {
         CronTask::Register(__CLASS__, 'sccm', 7 * DAY_TIMESTAMP,
            array('param' => 24, 'mode' => CronTask::MODE_EXTERNAL));
      }
   }

   static function uninstall() {
      CronTask::Unregister(__CLASS__);
   }

   static function cronSccm($task) {
      self::executeSync();
      return true;
   }

   static function cronInfo($name) {
      return array('description' => __("Interface - SCCM", "sccm"));
   }

   static function executeSync() {

      ini_set('max_execution_time', 0);
      $REP_XML = GLPI_PLUGIN_DOC_DIR.'/sccm/xml/';

      $PluginSccmConfig = new PluginSccmConfig();
      $PluginSccmConfig->getFromDB(1);

      $PluginSccmSccm = new PluginSccmSccm();

      if($PluginSccmConfig->getField('active_sync') == 1) {
         Toolbox::logInFile('sccm', "Inject start \n", true);

         $PluginSccmSccm->getDevices();
         Toolbox::logInFile('sccm', "getDevices OK \n", true);

         Toolbox::logInFile('sccm', "Generate XML start : " 
            . count($PluginSccmSccm->devices) . " files\n", true);

         foreach($PluginSccmSccm->devices as $device_values) {

            $PluginSccmSccmxml = new PluginSccmSccmxml($device_values);

            $PluginSccmSccmxml->setAccessLog();
            $PluginSccmSccmxml->setAccountInfos();
            $PluginSccmSccmxml->setHardware();
            $PluginSccmSccmxml->setOS();
            $PluginSccmSccmxml->setBios();
            $PluginSccmSccmxml->setProcessors();
            $PluginSccmSccmxml->setSoftwares();
            $PluginSccmSccmxml->setUsers();
            $PluginSccmSccmxml->setNetworks();
            $PluginSccmSccmxml->setDrives();

            $SXML = $PluginSccmSccmxml->sxml;

            $SXML->asXML($REP_XML.$PluginSccmSccmxml->device_id.".ocs");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $PluginSccmConfig->getField('fusioninventory_url'));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $SXML->asXML());
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_REFERER, $PluginSccmConfig->getField('fusioninventory_url'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $ch_result = curl_exec($ch);
            curl_close($ch);

            Toolbox::logInFile('sccm', "Ajout OK - ".$PluginSccmSccmxml->device_id." \n", true);
         }

      } else {
         echo __("Synchronization is disabled by configuration.", "sccm");
      }
   }

}
