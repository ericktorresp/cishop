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






if(!defined('LIVEADMIN')) exit; ini_set('display_errors',false); define('LIVEADMIN_VERSION','1.3.5'); if(LIVEADMIN_MASTER==false && LIVEADMIN_SLAVE==false) { define('LIVEADMIN_STANDALONE',true); } else { define('LIVEADMIN_STANDALONE',false); } if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])=='on') define('LIVEADMIN_SSL',true); else define('LIVEADMIN_SSL',false); define('LIVEADMIN_LITE',false); if(LIVEADMIN_STANDALONE) { define('LIVEADMIN_WC',LIVEADMIN_W.'/index.php'); define('LIVEADMIN_WT',LIVEADMIN_W.'/themes'); define('LIVEADMIN_WCC',LIVEADMIN_W.'/client.php'); define('LIVEADMIN_WOF',LIVEADMIN_W.'/images/on_off'); define('LIVEADMIN_WSND',LIVEADMIN_W.'/assets/snd_app'); define('LIVEADMIN_WR',LIVEADMIN_W.'/assets/rings'); if(!defined('LIVEADMIN_B')) { define('LIVEADMIN_B',preg_replace('/\/include$/','',dirname(__FILE__))); } define('LIVEADMIN_F',LIVEADMIN_B); define('LIVEADMIN_GO',LIVEADMIN_F.'/geoip/geolitecity.dat'); define('LIVEADMIN_FC',LIVEADMIN_F.'/include'); define('LIVEADMIN_FI',LIVEADMIN_F.'/images'); define('LIVEADMIN_FT',LIVEADMIN_F.'/templates'); define('LIVEADMIN_FQ',LIVEADMIN_F.'/include/sql'); define('LIVEADMIN_T',LIVEADMIN_F.'/themes'); define('LIVEADMIN_L',LIVEADMIN_F.'/languages'); define('LIVEADMIN_FOF',LIVEADMIN_F.'/images/on_off'); define('LIVEADMIN_FLS',LIVEADMIN_F.'/files'); define('LIVEADMIN_TEMP_FC_DEFAULT',LIVEADMIN_B.'/tmp'); define('LIVEADMIN_AST',LIVEADMIN_F.'/assets'); define('LIVEADMIN_R',LIVEADMIN_AST.'/rings'); define('LIVEADMIN_MASTER_URL',LIVEADMIN_W); define('LIVEADMIN_MASTER_ADMIN_URL',LIVEADMIN_MASTER_URL.'/index.php'); define('LIVEADMIN_MASTER_URL_LOGIN',LIVEADMIN_MASTER_URL.'/login.php'); define('LIVEADMIN_URL_CHAT',LIVEADMIN_W.'/client.php'); define('LIVEADMIN_URL_CALLBACK',LIVEADMIN_W.'/callback.php'); define('LIVEADMIN_CLIENT_BASE_URL',LIVEADMIN_MASTER_URL.'/client.php'); } define('LIVEADMIN_DEFAULT_THEME','default'); $_SERVER['CONTROL_MESSAGES'] = array ( 'CHAT_CLOSED_BY_CLIENT' => 'CS_1001', 'CHAT_CLOSED_BY_ADMIN' => 'CS_1002', 'CLIENT_TYPE_ON' => 'CS_1051', 'CLIENT_TYPE_OFF' => 'CS_1052', 'ADMIN_TYPE_ON' => 'CS_1061', 'ADMIN_TYPE_OFF' => 'CS_1062', 'TRANSFER_DEPT' => 'CS_1071', 'TRANSFER_AGENT' => 'CS_1072' ); define('LIVEADMIN_GFLAGS_HIDE_NEWS',1); foreach($_SERVER['CONTROL_MESSAGES'] as $a=>$v) { define($a,$v); } $temp_folders = array ( LIVEADMIN_TEMP_FC_DEFAULT, ini_get('session.save_path'), ini_get('upload_tmp_dir'), '/tmp' ); foreach($temp_folders as $a) { if($a!='' && is_writable($a)) { define('LIVEADMIN_TEMP_FC',$a); break; } } if (get_magic_quotes_gpc()) { $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST); while (list($key, $val) = each($process)) { foreach ($val as $k => $v) { unset($process[$key][$k]); if (is_array($v)) { $process[$key][stripslashes($k)] = $v; $process[] = &$process[$key][stripslashes($k)]; } else { $process[$key][stripslashes($k)] = stripslashes($v); } } } unset($process); } ?>