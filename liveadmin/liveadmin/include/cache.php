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

if(!defined('LIVEADMIN')) exit;
define("LIVEADMIN_CACHE_CLEANUP_SEC",7*24*3600);
class LV_Cache
{
	function __construct()
	{
	}
	function __destruct()
	{
	}
	function GetCache($cache_id,$valid_period_s)
	{
		$RV = false;
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'cache';
		$dtm = time()-$valid_period_s;
		$cache_id_esc = mysql_real_escape_string($cache_id,$dbh);
		$res = mysql_query("SELECT * FROM $tbl WHERE cache_id='$cache_id_esc' AND update_date>$dtm ",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV = $this->CacheDecode($req['cache_text']);
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function SetCache($cache_id,$data)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'cache';
		$dtm_cleanup = time()-LIVEADMIN_CACHE_CLEANUP_SEC;
		mysql_query("DELETE FROM $tbl WHERE update_date<$dtm_cleanup ",$dbh);
		$dtm = time();
		$cache_id_esc = mysql_real_escape_string($cache_id,$dbh);
		$data_enc = $this->CacheEncode($data);
		mysql_query("UPDATE $tbl SET cache_text='$data_enc', update_date=$dtm WHERE cache_id='$cache_id_esc' ",$dbh);
		if(mysql_affected_rows($dbh)==0)
		{
			mysql_query("INSERT INTO $tbl SET cache_text='$data_enc', update_date=$dtm, cache_id='$cache_id_esc' ",$dbh);
		}
		mysql_close($dbh);
	}
	function DeleteCache($cache_id)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'cache';
		$cache_id_esc = mysql_real_escape_string($cache_id,$dbh);
		mysql_query("DELETE FROM $tbl WHERE cache_id='$cache_id_esc' ",$dbh);
		mysql_close($dbh);
	}
	function CacheEncode($s)
	{
		$comp = false;
		if(is_callable('gzcompress')) $comp = true;
		$RV = '~ENC~V1'.(($comp)?'+':'-').'~';
		if($comp) $RV .= str_replace(array('+','/','='),array('_P','_S','_E'),base64_encode(gzcompress($s,5)));
		else $RV .= str_replace(array('+','/','='),array('_P','_S','_E'),base64_encode($s));
		return($RV);
	}
	function CacheDecode($s)
	{
		if(substr($s,0,5)!='~ENC~') return(decode64($s));
		if(substr($s,0,7)=='~ENC~V1')
		{
			$comp = false;
			if(substr($s,7,1)=='+') $comp = true;
			if($comp) return(gzuncompress(base64_decode(str_replace(array('_P','_S','_E'),array('+','/','='),substr($s,9)))));
			else return(base64_decode(str_replace(array('_P','_S','_E'),array('+','/','='),substr($s,9))));
		}
		return($s);
	}
}
?>