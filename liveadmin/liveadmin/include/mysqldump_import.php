<?php

/***************************************************************
* Live Admin Standalone
* Copyright 2008-2011 Dayana Networks Ltd.
* All rights reserved, Live Admin  is  protected  by  Canada and
* International copyright laws. Unauthorized use or distribution
* of  Live Admin  is  strictly  prohibited,  violators  will  be
* prosecuted. To  obtain  a license for using Live Admin, please
* register at http://www.liveadmin.net/register.php
*
* For more information please refer to Live Admin official site:
*    http://www.liveadmin.net
*
* Translation service provided by Google Inc.
***************************************************************/






 if(!defined('LIVEADMIN')) exit; class MySQLDumpImport { var $database = null; var $Error; function __construct() { $this->Error = array(); $this->dbh = mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0'); mysql_select_db(LIVEADMIN_DB_DATABASE,$this->dbh); } function __destruct() { mysql_close($this->dbh); } function importDumpFile($dump_file) { $fs = file_get_contents($dump_file); $fs = str_replace('XXX-TABLE_PREFIX-XXX',LIVEADMIN_DB_PREFIX,$fs); $this->importDumpString($fs); } function importDumpString ($dump_str) { if ($this->dbh !== false) { $sqlFile = $dump_str; $sqlFile = str_replace("\r","%BR%",$sqlFile); $sqlFile = str_replace("\n","%BR%",$sqlFile); $sqlFile = str_replace("%BR%%BR%","%BR%",$sqlFile); $sqlArray = explode('%BR%', $sqlFile); $sqlArrayToExecute; foreach ($sqlArray as $stmt) { $stmt = $this->is_comment($stmt); if ($stmt != '') $sqlArrayToExecute[] = $stmt; } $sqlFile = implode("%BR%",$sqlArrayToExecute); unset($sqlArrayToExecute); $sqlArray = explode(';%BR%', $sqlFile); unset($sqlFile); foreach ($sqlArray as $stmt){ $stmt = str_replace("%BR%"," ",$stmt); $stmt = trim($stmt); $result = mysql_query($stmt,$this->dbh); if (!$result) { $this->Error[] = '['.mysql_errno($this->dbh).'] '.mysql_error($this->dbh); } } } else { $this->Error[] = "[0] MySQL server access denied, please check the access data login"; } } function is_comment($text) { if ($text != ""){ $fL = $text[0]; $sL = $text[1]; switch($fL){ case "#": $text = ""; break; case "/": if ($sL == "*") $text = ""; break; case "-": if ($sL == "-") $text = ""; break; } } return $text; } } ?>