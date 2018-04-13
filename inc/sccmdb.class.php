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

      $connectionOptions = array(
          "Database" => $dbname,
          "Uid" => $user,
          "PWD" => $password
      );

      $conn = sqlsrv_connect( $host, $connectionOptions );
      if( $conn === false ) {
             die($this->FormatErrors( sqlsrv_errors()));
      }

      return true;
   }

   function disconnect() {

      sqlsrv_close($this->dbconn);

   }

   function exec_query($query) {

      $result = sqlsrv_query($this->dbconn, $query) or die('Query error : ' . print_r(sqlsrv_errors(), true));
      if ( $result == FALSE ){
         die( FormatErrors( sqlsrv_errors()));
      }
      return $result;

   }

   function FormatErrors( $errors ){  
     
      foreach ( $errors as $error ){  
         $debug   = "";
         $state   = "SQLSTATE: ".$error['SQLSTATE'];  
         $code    = "Code: ".$error['code'];  
         $message = "Message: ".$error['message'];  

         echo $state."</br>".$code."<br>".$message."<br>";
         Toolbox::logInFile("sccm", $state.PHP_EOL.$code.PHP_EOL.$message.PHP_EOL);
      }

   } 

}

