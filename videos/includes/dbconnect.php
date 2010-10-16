<?php

	/**
	* @Software : ClipBucket
	* @License : CBLA
	* @version :ClipBucket v2
	*/

	$BDTYPE = 'mysql';
	//Database Host
	$DBHOST = 'localhost';
	//Database Name
	$DBNAME = 'hxxpscom_video';
	//Database Username
	$DBUSER = 'root';
	//Database Password
	$DBPASS = '2908262';
	//Setting Table Prefix
	define('TABLE_PREFIX','cb_');

	require 'adodb/adodb.inc.php';

	$db             = ADONewConnection($BDTYPE);
	$db->debug      = false;
	$db->charpage   = 'cp_utf8';
	$db->charset    = 'utf8';
	if(!$db->Connect($DBHOST, $DBUSER, $DBPASS, $DBNAME))
	{
	    exit($db->ErrorMsg());
	}
	$db->Connect($DBHOST, $DBUSER, $DBPASS, $DBNAME);
	
	$db->Execute('SET NAMES utf8');
	$db->Execute('SET CHARACTER SET utf8');
	$db->Execute('SET COLLATION_CONNECTION="utf8_general_ci"');

?>