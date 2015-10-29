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

class PluginSccmSccmdb {

   var $dbconn;

   function connect() {

      $PluginSccmConfig = new PluginSccmConfig();
         $PluginSccmConfig->getFromDB(1);

      $host = $PluginSccmConfig->getField('sccmdb_host');
      $dbname = $PluginSccmConfig->getField('sccmdb_dbname');
      $user = $PluginSccmConfig->getField('sccmdb_user');

      $password = $PluginSccmConfig->getField('sccmdb_password');
      $password = Toolbox::decrypt($password, GLPIKEY);

      // If its SQLSrv extension for PHP
      if(function_exists('sqlsrv_connect')) {

         $dbinfo = array(
            'Database' => $dbname,
            'UID' => $user,
            'PWD' => $password
         );

         $this->dbconn = sqlsrv_connect($host,$dbinfo)
                           or die('Connection error : ' . print_r(sqlsrv_errors(), true));

      }
      // Else if its MSSQL extension for PHP
      elseif(function_exists('mssql_connect')) {

         $this->dbconn = mssql_connect($host,$user,$password)
                        or die('Connection error : ' . mssql_get_last_message());

         if (!mssql_select_db($dbname, $this->dbconn)) {
            die('Unable to connect do DB!' . mssql_get_last_message());

      }
      else {
         die('Cannot connect to unknown MS-SQL extension');
      }


      return true;
   }

   function disconnect() {

      // If its SQLSrv extension for PHP
      if(function_exists('sqlsrv_close')) {
         sqlsrv_close($this->dbconn);
      }
      // Else if its MSSQL extension for PHP
      elseif(function_exists('mssql_close')) {
         mssql_close($this->dbconn);
      }
      else {
         die('Cannot close connection for unknown MS-SQL extension');
      }
   }

   function exec_query($query) {
      // If its SQLSrv extension for PHP
      if(function_exists('sqlsrv_query')) {
         $result = sqlsrv_query($this->dbconn, $query) or die('Query error : ' . print_r(sqlsrv_errors(), true));
      }
      // Else if its MSSQL extension for PHP
      elseif(function_exists('mssql_query')) {
         $result = mssql_query($query) or die('Query error : ' . mssql_get_last_message());
      }
      else {
         die('Cannot execute query to unknown MS-SQL extension');
      }

      return $result;
   }

}

?>