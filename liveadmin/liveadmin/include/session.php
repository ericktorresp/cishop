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






 if(!defined('LIVEADMIN')) exit; class LV_Session { private $dbh; private $tbl; private $session_id; function __construct($session_id=false) { if($session_id===false) { if(isset($_REQUEST['lv_sid'])) $session_id = $_REQUEST['lv_sid']; else $session_id = RandomString(32); } $_SERVER['lv_sid'] = $session_id; $this->session_id = $session_id; $this->dbh = mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0'); mysql_select_db(LIVEADMIN_DB_DATABASE,$this->dbh); $this->tbl = LIVEADMIN_DB_PREFIX.'session'; } function __destruct() { mysql_close($this->dbh); } function Start() { mysql_query("INSERT INTO ".$this->tbl." SET id='".$this->session_id."', last_act='".time()."', data='".serialize(array())."' ",$this->dbh); } function Destroy() { mysql_query("DELETE FROM ".$this->tbl." WHERE id='".$this->session_id."'",$this->dbh); } function Cleanup() { $last_act_expire = time()-(12*3600); mysql_query("DELETE FROM ".$this->tbl." WHERE last_act<$last_act_expire  ",$this->dbh); } function Get() { $RV = array(); $res = mysql_query("SELECT * FROM ".$this->tbl." WHERE id='".$this->session_id."'",$this->dbh); if($res) { while($req = mysql_fetch_array($res)) { $RV = unserialize($req['data']); $RV['last_act'] = $req['last_act']; break; } } return($RV); } function Set($data) { $dt = mysql_real_escape_string(serialize($data),$this->dbh); mysql_query("UPDATE ".$this->tbl." SET last_act='".time()."', data='".$dt."' WHERE id='".$this->session_id."'",$this->dbh); } function UpdateTime() { mysql_query("UPDATE ".$this->tbl." SET last_act='".time()."' WHERE id='".$this->session_id."'",$this->dbh); } } ?>