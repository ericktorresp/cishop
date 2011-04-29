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






define('LIVEADMIN',true);
require_once('config.php');
include_once('json.php');
define('LIVEADMIN_CON_D' ,'0123456789');
define('LIVEADMIN_CON_L' ,'abcdefghijklmnopqrstuvwxyz');
define('LIVEADMIN_CON_U' ,'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
if(isset($_REQUEST['cpp_pack']) && $_REQUEST['cpp_pack']==1)
{
	define('LIVEADMIN_CPP',true);
}
else
{
	define('LIVEADMIN_CPP',false);
}
function Redirect($s,$url=false,$code=302)
{
	if($url===false) $url = LIVEADMIN_WC;
	if(substr($url,-4)!='.php')
	{
		$url = $url.'/';
	}
	if(strpos($s,'?')!==false) $s .= '&rnd_'.rand(10,99).'='.rand(10000,99999);
	if(isset($_SERVER['lv_sid']))
	{
		if(strpos($s,'?')!==false) $s .= '&lv_sid='.$_SERVER['lv_sid'];
		else $s .= '?lv_sid='.$_SERVER['lv_sid'];
	}
	if(isset($_REQUEST['ajx']) && $_REQUEST['ajx']==1)
	{
		if(LIVEADMIN_CPP)
		{
			$urlp = parse_url($url."$s");
			parse_str($urlp['query'],$urlps);
			foreach($urlps as $a=>$v)
			{
				$urlp['q_'.$a] = $v;
			}
			PrintJSonPack(array('status'=>1,'redirect'=>$urlp));
		}
		else
		{
			PrintJSonPack(array('status'=>1,'redirect'=>$url."$s"));
		}
		exit();
	}
	else
	{
		header("Location: ".$url."$s\n\n",true,$code);
		exit();
	}
}
function encode64($s)
{
	$rv = base64_encode($s);
	$rv = str_replace(array("+","/","="),array("_PL","_SL","_EQ"),$rv);
	return strrev($rv);
}
function decode64($s)
{
	$rv = strrev($s);
	$rv = str_replace(array("_PL","_SL","_EQ"),array("+","/","="),$rv);
	$rv = base64_decode($rv);
	return($rv);
}
function LoadLang($language)
{
	include_once(dirname(__FILE__).'/lang.php');
	if(!isset($_SERVER['lang_obj'][$language]))
	{
		$_SERVER['lang_obj'][$language] = new LV_Lang($language);
	}
	return($_SERVER['lang_obj'][$language]);
}
function lv_property_exists($class,$property)
{
	if (!function_exists('property_exists'))
	{
		if (is_object($class)) $class = get_class($class);
		return array_key_exists($property, get_class_vars($class));
	}
	else
	{
		return property_exists($class,$property);
	}
}
function liveadmin_encode64($s)
{
	if(strpos($s,'<s5lang')!==false || strpos($s,'&lt;s5lang')!==false)
	{
		if(isset($_SERVER['uinfo']['language']))
		{
			$lang = LoadLang($_SERVER['uinfo']['language']);
			$s = $lang->Translate($s);
		}
	}
	return(str_replace(array('=','+','/'),array('_E','_P','_S'),base64_encode($s)));
}
function liveadmin_decode64($s)
{
	if(is_array($s))
	{
		foreach($s as $a=>$v)
		{
			$s[$a] = base64_decode(str_replace(array('_E','_P','_S'),array('=','+','/'),$v));
		}
		return($s);
	}
	else
	{
		return(base64_decode(str_replace(array('_E','_P','_S'),array('=','+','/'),$s)));
	}
}
function MySQLHex($s,$stripslash=true,$replacequote=true,$add0x=true)
{
	$ANT = $s;
	if($stripslash) $ANT = stripslashes($ANT);
	if($replacequote) $ANT = str_replace('"',"'",$ANT);
	$ANS = "";
	if($add0x) $ANS .= "0x";
	for($i=0;$i< strlen($ANT);$i++)
	{
		$RVL = dechex(ord($ANT{$i}));
		if(strlen($RVL)==1) $RVL = "0".$RVL;
		$ANS .= $RVL;
	}
	if($add0x && strlen($ANT)==0) $ANS .= "0";
	return($ANS);
}
function AjaxPack($ar)
{
	$RV = '';
	if(is_array($ar))
	{
		foreach($ar as $a=>$v)
		{
			if($RV!='') $RV .= '|';
			$a = str_replace(array('|',',','<','>'),array('#_CP1-#','#_CP2-#','#_CP3#','#_CP4#'),$a);
			$v = str_replace(array('|',',','<','>'),array('#_CP1-#','#_CP2-#','#_CP3#','#_CP4#'),$v);
			$RV .= $a.','.$v;
		}
	}
	else
	{
		$RV = $ar;
	}
	return('<--START-->'.$RV.'<--END-->');
}
function CppPack($ar)
{
	$RV = '<XML ID="lvo">'."\n";
	_cpp_pack_array($ar,$RV);
	$RV .= '</XML>';
	return($RV);
}
function _cpp_pack_array($ar,&$r)
{
	foreach($ar as $a=>$v)
	{
		if(is_numeric($a)) $a = '_'.$a;
		if(is_array($v))
		{
			$r .= '<'._cpp_pack_trans($a).'>'."\n";
			_cpp_pack_array($v,$r);
			$r .= '</'._cpp_pack_trans($a).'>'."\n";
		}
		else
		{
			$r .= '<'._cpp_pack_trans($a).'>'._cpp_pack_trans($v,true).'</'._cpp_pack_trans($a).'>'."\n";
		}
	}
}
function _cpp_pack_trans($s,$encode=false)
{
	if($encode && !is_numeric($s)) $s = '#_ENC-#'.liveadmin_encode64($s);
	return(str_replace(array('<','>'),array('#_CP1-#','#_CP2-#'),$s));
}
function JsonPack($ar,$protect=true)
{
	if(LIVEADMIN_CPP)
	{
		$pack = CppPack($ar);
	}
	else
	{
		$json = new Services_JSON();
		$pack = $json->encode($ar);
	}
	if($protect) return('<--START-->'.$pack.'<--END-->');
	else return($pack);
}
function PrintJsonPack($ar,$protect=true)
{
	header('Content-Type: application/json');
	print(JsonPack($ar,$protect));
}
function PrintJsonPackEnc($ar,$protect=true)
{
	header('Content-Type: text/html');
	$RV = liveadmin_encode64(JsonPack($ar,false));
	if($protect) $RV = '[[['.$RV.']]]';
	print($RV);
}
function Array2String($ar)
{
	$RV = '';
	_array2string($ar,$RV,0);
	return($RV);
}
function _array2string($ar,&$rv,$level)
{
	if(is_array($ar))
	{
		foreach($ar as $a=>$v)
		{
			$rv .= str_repeat('    ',$level).$a;
			if(!is_array($v)) $rv .= ' => '.$v."\n";
			else
			{
				$rv .= "\n".str_repeat('    ',$level)."{\n";
				_array2string($v,$rv,$level+1);
				$rv .= str_repeat('    ',$level)."}\n";
			}
		}
	}
	else
	{
		$rv .= str_repeat('    ',$level).$ar."\n";
	}
}
function AddArray($base,$add,$prefix='')
{
	foreach($add as $a=>$v)
	{
		$base[$prefix.$a] = $v;
	}
	return($base);
}
function Key2ID($key)
{
	$key = strtoupper($key);
	$vpos = strpos($key,'V');
	$mpos = strpos($key,'M');
	return(intval(hexdec(substr($key,$vpos+1,$mpos-$vpos-1))/1843));
}
function ID2Key($id)
{
	return(strtoupper('L'.dechex($id*12854).'V'.dechex($id*1843).'M'.dechex($id*498)));
}
function RealTime()
{
	$micro = substr(number_format(microtime(true),10,'.',''),0,18);
	$n = $micro.mt_rand(111,999);
	return(number_format($n,10,'.',''));
}
function DateIfExists($date,$time_zone, $include_time=false)
{
	$date = $date*1;
	$format = "d-M-Y";
	if($include_time) $format = "d-M-Y H:i:s";
	if($date>0)
	{
		return(lv_date($format,$date,$time_zone));
	}
	return('n/a');
}
function lv_unserialize($s)
{
	$s = preg_replace_callback('/s:(\d+):\"(.*)\"/ismU',"lv_unserialize_callback",$s);
	return(unserialize($s));
}
function lv_unserialize_callback($m)
{
	return('s:'.strlen($m[2]).':"'.$m[2].'"');
}
function ArrayMember($array,$member,$default='')
{
	if(!is_array($array)) $array = @unserialize($array);
	if(isset($array[$member])) return($array[$member]);
	return($default);
}
function TimeDiffText($seconds,$complete=false)
{
	if($complete==false)
	{
		$min = $seconds/60;
		if($min<60) return($min.' <s5lang>minutes</s5lang>');
		if($min<(24*60)) return(intval($min/60).' <s5lang>hours</s5lang>');
		return(intval($min/(24*60)).' <s5lang>days</s5lang>');
	}
	else
	{
		$days = intval($seconds/(3600*24));
		$hours = intval(($seconds-($days*3600*24))/3600);
		$min = intval(($seconds-($days*3600*24)-($hours*3600))/60);
		if($days>0) return($days.'d '.$hours.'h '.$min.'m ');
		if($hours>0) return($hours.'h '.$min.'m ');
		if($min>0) return($min.'m ');
		return($seconds.'s');
	}
}
function lv_date($format,$timestamp,$time_zone)
{
	$RV = gmdate($format,$timestamp+($time_zone*60));
	if($format!='r') return($RV);
	if($time_zone<0) $sign = '-';
	else $sign = '+';
	$time_zone = abs($time_zone);
	$RV = str_replace('+0000',$sign.sprintf("%02d",intval($time_zone/60)).sprintf("%02d",$time_zone-(intval($time_zone/60)*60)),$RV);
	return($RV);
}
function lv_mktime($hour,$min,$sec,$month,$day,$year,$time_zone)
{
	return(gmmktime($hour,$min,$sec,$month,$day,$year)-($time_zone*60));
}
function RandomString($len)
{
	$all = LIVEADMIN_CON_D.LIVEADMIN_CON_L.LIVEADMIN_CON_U;
	$BC = str_repeat(str_shuffle($all),(intval($len/strlen($all))+5)*2);
	$BC = str_shuffle(base64_encode($BC));
	$BC = str_shuffle(base64_encode($BC));
	$BC = str_replace(array('+','-','/','='),'',$BC);
	return(substr($BC,0,$len));
}
function Second2Text($in)
{
	$h = intval($in/3600);
	$m = intval(($in-($h*3600))/60);
	$s = intval($in-($h*3600)-($m*60));
	if($h>0) $r = $h.':'.sprintf("%02d",$m).':'.sprintf("%02d",$s);
	elseif($m>0) $r = $m.':'.sprintf("%02d",$s);
	else $r = $s;
	return($r.'s');
}
function IsValidEmailSyntax($email)
{
	$isValid = true;
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex)
	{
		$isValid = false;
	}
	else
	{
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64)
		{
			$isValid = false;
		}
		else if ($domainLen < 1 || $domainLen > 255)
		{
			$isValid = false;
		}
		else if ($local[0] == '.' || $local[$localLen-1] == '.')
		{
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $local))
		{
			$isValid = false;
		}
		else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		{
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $domain))
		{
			$isValid = false;
		}
		else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local)))
		{
			if (!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local)))
			{
				$isValid = false;
			}
		}
	}
	return $isValid;
}
function GetTableInfo($field,$where,$table)
{
	$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
	mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
	$tbl = LIVEADMIN_DB_PREFIX.$table;
	$sfield = mysql_real_escape_string($field,$dbh);
	$swhere = mysql_real_escape_string($where,$dbh);
	$res = mysql_query("SELECT * FROM $tbl WHERE $sfield=\"$swhere\" ",$dbh);
	$RV = array();
	if($res)
	{
		while($req = mysql_fetch_array($res))
		{
			if($req[$field]==$where)
			{
				$RV = $req;
				break;
			}
		}
	}
	mysql_close($dbh);
	if(count($RV)==0) return false;
	return $RV;
}
function GetUserInfo($field,$where,$table='users')
{
	$UInfo = GetTableInfo($field,$where,$table);
	if($UInfo!==false) $UInfo['hide_news'] = getGFlags($UInfo['gflags'], LIVEADMIN_GFLAGS_HIDE_NEWS);
	return($UInfo);
}
function getGFlags($gflags, $bit)
{
	$RV = strtolower(substr($gflags,$bit,1));
	if($RV!='y') $RV = 'n';
	return($RV);
}
function setGFlags(&$gflags, $bit)
{
	$gflags = str_pad($gflags,45,'o',STR_PAD_RIGHT);
	$gflags = substr_replace($gflags,'y',$bit,1);
	return($RV);
}
function unsetGFlags(&$gflags, $bit)
{
	$gflags = str_pad($gflags,45,'o',STR_PAD_RIGHT);
	$gflags = substr_replace($gflags,'n',$bit,1);
	return($RV);
}
function GetSiteInfo($field,$where,$table='sites')
{
	$TInfo = GetTableInfo($field,$where,$table);
	if(LIVEADMIN_STANDALONE)
	{
		$TInfo['license_expiry_date'] = $TInfo['expiry_date'];
		$TInfo['license_status'] = $TInfo['in_trial'];
		$TInfo['license_retry_check_count'] = $TInfo['refid'];
		$TInfo['site_status']=1;
		$TInfo['serverid']=0;
		$TInfo['demo_mode']=0;
		$TInfo['expiry_date']=time()+(365*24*3600);
		$TInfo['refid'] = 0;
		$lc = new LV_L();
		$lc->ModSInfo($TInfo);
		unset($lc);
	}
	return($TInfo);
}
function GetServerInfo($field,$where,$table='servers')
{
	return(GetTableInfo($field,$where,$table));
}
function SetSiteLastAct($siteid)
{
	$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
	mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
	$tbl = LIVEADMIN_DB_PREFIX.'sites';
	$siteid_esc = mysql_real_escape_string($siteid,$dbh);
	$last_act = time();
	$res = mysql_query("UPDATE $tbl SET last_act=$last_act WHERE siteid=\"$siteid_esc\" ",$dbh);
	mysql_close($dbh);
}
function GetUsersList($siteid)
{
	$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
	mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
	$tbl = LIVEADMIN_DB_PREFIX.'users';
	$siteid_esc = mysql_real_escape_string($siteid,$dbh);
	$res = mysql_query("SELECT * FROM $tbl WHERE siteid=\"$siteid_esc\" ORDER BY userid ",$dbh);
	$RV = array();
	if($res)
	{
		while($req = mysql_fetch_array($res))
		{
			$RV[$req['userid']] = $req;
		}
	}
	mysql_close($dbh);
	return $RV;
}
function GetServersList()
{
	$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
	mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
	$tbl = LIVEADMIN_DB_PREFIX.'servers';
	$res = mysql_query("SELECT * FROM $tbl ORDER BY serverid",$dbh);
	$RV = array();
	if($res)
	{
		while($req = mysql_fetch_array($res))
		{
			$RV[$req['serverid']] = $req;
		}
	}
	mysql_close($dbh);
	return $RV;
}
function lv_version_string($include_extra = true)
{
	if(LIVEADMIN_STANDALONE)
	{
		$r = '<strong>Unregistered</strong>';
		if($_SERVER['sinfo']['in_trial']==0) $r = 'Registered';
		if(LIVEADMIN_LITE) $r = 'Lite';
	}
	else
	{
		$r = '<strong>Trial</strong>';
		if($_SERVER['sinfo']['in_trial']==0) $r = '';
	}
	$s = 'LiveAdmin '.LIVEADMIN_VERSION;
	if($include_extra) $s .= ' '.$r;
	return($s);
}
function lv_get_curl_useragent()
{
	return('Mozilla/5.0 (compatible;
E8 live support/'.LIVEADMIN_VERSION.';
+http://hxxp.us/)');
}
function lv_get_google_maps_api()
{
	$RV = 'ABQIAAAAMEVzXgO6bXEKrLubNDjV-BTl9SbImEYrw5mLzKpkj2H5bOiIgRTQy0LN0eGgyA1o-QY9xPd9Xatl4Q';
	if(!LIVEADMIN_STANDALONE)
	{
		return($RV);
	}
	if($_SERVER['sinfo']['chat_window_options']!='')
	{
		$chat_window_options = unserialize($_SERVER['sinfo']['chat_window_options']);
		if(isset($chat_window_options['google_map_api_key']) && $chat_window_options['google_map_api_key']!='' && strlen($chat_window_options['google_map_api_key'])>10)
		{
			$RV = str_replace(array("\r","\n","\s"),array("","",""),$chat_window_options['google_map_api_key']);
		}
	}
	return($RV);
}
function lv_get_departments($sinfo)
{
	$RV = array();
	if(isset($sinfo['chat_window_options']) && $sinfo['chat_window_options']!='')
	{
		$cwo = unserialize($sinfo['chat_window_options']);
		if(isset($cwo['departments']) && is_array($cwo['departments']))
		{
			$RV = $cwo['departments'];
		}
	}
	return($RV);
}
function DoLogin($user,$pass)
{
	require_once('session.php');
	$session = new LV_Session();
	$session->Cleanup();
	$user = strtolower(trim($user));
	$UInfo = GetUserInfo('username',$user);
	if($UInfo===false || trim($pass)=='' || $UInfo['password']!=crypt($pass,$UInfo['password']))
	{
		Redirect('?act=login&e=501',LIVEADMIN_MASTER_ADMIN_URL);
	}
	$SInfo = GetSiteInfo('siteid',$UInfo['siteid']);
	$SInfoStatus = IsValid_SInfo($SInfo);
	if($SInfoStatus!==true)
	{
		Redirect('?act=login&e='.$SInfoStatus,LIVEADMIN_MASTER_ADMIN_URL);
	}
	elseif($UInfo['ac_status']==0)
	{
		Redirect('?act=login&e=502',LIVEADMIN_MASTER_ADMIN_URL);
	}
	else
	{
		$pass_enc = encode64($pass);
		$session->Start();
		$session->Set(array ( 'valid'=>1, 'user'=>$user, 'pass'=>$UInfo['password'], 'uniq'=>md5($user.'-'.$pass_enc) ));
		Redirect('?act=home');
		return true;
	}
}
function DoLogout()
{
	require_once('session.php');
	$session = new LV_Session();
	$session->Destroy();
	Redirect("?act=login&e=601",LIVEADMIN_MASTER_ADMIN_URL);
}
function CheckLogin()
{
	if(defined('LIVEADMIN_CHECK_LOGIN_FUNC')) return(LIVEADMIN_CHECK_LOGIN_FUNC);
	define('LIVEADMIN_CHECK_LOGIN_FUNC',_RealCheckLogin());
	return(LIVEADMIN_CHECK_LOGIN_FUNC);
}
function _RealCheckLogin()
{
	require_once('session.php');
	$session = new LV_Session();
	$session_info = $session->Get();
	if(!isset($session_info['valid']) || $session_info['valid']!=1)
	{
		$session->Destroy();
		Redirect("?act=login",LIVEADMIN_MASTER_ADMIN_URL);
		return false;
	}
	elseif($session_info['last_act']<time()-(4*3600))
	{
		$session->Destroy();
		Redirect("?act=login&e=701",LIVEADMIN_MASTER_ADMIN_URL);
		return false;
	}
	$user = $session_info['user'];
	$UInfo = GetUserInfo('username',$user);
	if($UInfo===false || trim($session_info['pass'])=='' || $UInfo['password']!=$session_info['pass'])
	{
		$session->Destroy();
		Redirect('?act=login&e=501',LIVEADMIN_MASTER_ADMIN_URL);
	}
	$SInfo = GetSiteInfo('siteid',$UInfo['siteid']);
	$SInfoStatus = IsValid_SInfo($SInfo);
	if($SInfoStatus!==true)
	{
		$session->Destroy();
		Redirect('?act=login&e='.$SInfoStatus,LIVEADMIN_MASTER_ADMIN_URL);
	}
	elseif($UInfo['ac_status']==0)
	{
		$session->Destroy();
		Redirect('?act=login&e=502',LIVEADMIN_MASTER_ADMIN_URL);
	}
	else
	{
		$session->UpdateTime();
		$SInfo['key'] = ID2Key($SInfo['siteid']);
		$_SERVER['uinfo'] = $UInfo;
		$_SERVER['sinfo'] = $SInfo;
		return true;
	}
}
function IsValid_SInfo($SInfo)
{
	if($SInfo===false)
	{
		return(503);
	}
	elseif($SInfo['site_status']==0)
	{
		return(504);
	}
	elseif($SInfo['site_status']==2)
	{
		return(505);
	}
	return(true);
}
function ChatWindowOptions($o)
{
	$default = array ( 'mainframe_width'=>500, 'mainframe_height'=>350 );
	foreach($default as $a=>$v)
	{
		if(!isset($o[$a])) $o[$a] = $v;
	}
	return($o);
}
function Style2Attr($style)
{
	$style = str_replace(array("\r","\n"),"",$style);
	$style = str_replace('"',"'",$style);
	$style = preg_replace('/\s*([;\{\}])\s*/','\\1',$style);
	preg_match_all('/\.(.*)\{(.*)\}/ismU',$style,$m);
	$RV = array();
	foreach($m[1] as $a=>$v)
	{
		preg_match_all('/\s*(.*)\s*:\s*(.*)\s*;/ismU',$m[2][$a],$n);
		$RV[$v] = array();
		foreach($n[1] as $a2=>$v2)
		{
			$v2 = preg_replace('/-([a-z])/e',"strtoupper('\\1')",strtolower($v2));
			$RV[$v][$v2] = $n[2][$a2];
		}
	}
	return($RV);
}
function liveadmin_implode($array,$prefix_key,$suffix_key,$key2value,$prefix_value,$suffix_value,$delimeter)
{
	$r = array();
	foreach($array as $a=>$v)
	{
		$r[] = $prefix_key.$a.$suffix_key.$key2value.$prefix_value.$v.$suffix_value;
	}
	return(implode($delimeter,$r));
}
function GetCountries()
{
	$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
	mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
	$tbl = LIVEADMIN_DB_PREFIX.'country';
	$res = mysql_query("SELECT * FROM $tbl",$dbh);
	$RV = array();
	if($res)
	{
		while($req = mysql_fetch_array($res))
		{
			$RV[$req['code']] = $req['name'];
		}
	}
	mysql_close($dbh);
	return($RV);
}
function GetStates()
{
	$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
	mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
	$tbl = LIVEADMIN_DB_PREFIX.'state';
	$res = mysql_query("SELECT * FROM $tbl",$dbh);
	$RV = array();
	if($res)
	{
		while($req = mysql_fetch_array($res))
		{
			$RV[$req['code']] = $req['name'];
		}
	}
	mysql_close($dbh);
	return($RV);
}
function DebugLog($s)
{
	if(is_array($s)) $s = print_r($s,true);
	$fi = fopen('/tmp/lv_debug_log','a+');
	fwrite($fi,'['.date('Y/m/d H:i:s').'] '.$s."\n");
	fclose($fi);
}
function StripComments($s)
{
	$s = preg_replace('/\/\*.*\*\//ismU','',$s);
	return($s);
}
function StripHTML($s)
{
	$s = str_replace(array('<','>'),array('&lt;','&gt;'),$s);
	return($s);
}
function CheckEnc($s)
{
	if(substr($s,0,5)!='~ENC[') return($s);
	if(preg_match('/~ENC\[(.*)\]ENC~/ismU',$s,$m))
	{
		return(liveadmin_decode64($m[1]));
	}
	else
	{
		return($s);
	}
}
function lv_is_utf8($s)
{
	$sa = str_split($s);
	for($i=0;$i<count($sa);$i++)
	{
		$sa[$i] = ord($sa[$i]);
	}
	$len = count($sa);
	for($i=0;$i<$len;$i++)
	{
		if($i+1<$len && ($sa[$i]&192)==192 && ($sa[$i+1]&128)==128) return true;
		if($i+2<$len && ($sa[$i]&224)==224 && ($sa[$i+1]&128)==128 && ($sa[$i+2]&128)==128) return true;
		if($i+3<$len && ($sa[$i]&240)==240 && ($sa[$i+1]&128)==128 && ($sa[$i+1]&128)==128 && ($sa[$i+1]&128)==128 ) return true;
	}
	return(false);
}
function lv_convert_utf8($s)
{
	return(utf8_encode($s));
}
function NoMultiLine($s)
{
	return(str_replace(array("\n","\r"),' ',$s));
}
function AutoLink($s)
{
	$s = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
	$s = preg_replace('@(ftp://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
	return($s);
}
class LV_L
{
	function __construct()
	{
	}
	function __destruct()
	{
	}
	function IsValid($lic,$siteid)
	{
		$RV = false;
		$li = $this->Dec($lic);
		if($li['type']==2 && $siteid==$li['siteid']) $RV = true;
		if($li['type']==3) $RV = true;
		//return($RV);
		return true;
	}
	function ModSInfo(&$SInfo)
	{
		// 		if(strlen($SInfo['license'])<10 || ($SInfo['license_expiry_date']<time()+(2*3600) && $SInfo['license_retry_check_count']>10 ))
		// 		{
		// 			$SInfo['in_trial'] = 1;
		// 			$SInfo['license_status'] = 1;
		// 		}
		// 		if(LIVEADMIN_LITE)
		// 		{
		$SInfo['in_trial'] = 0;
		$SInfo['license_status'] = 0;
		// 		}
		}
		function Dec($lic)
		{
			$lic = InputFilter(strtoupper($lic),LIVEADMIN_CON_U);
			$licb = array ( 'type'=>0, 'sit'.'eid'=>0 );
			if($lic!='' && strlen($lic)==25)
			{
				$lic_or = array();
				for($i=0;$i<25;$i++)
				{
					$lic_or[]= ord(substr($lic,$i,1));
				}
				$nmof = $lic_or[1]-65;
				if($nmof<0 || $nmof>24) return($licb);
				$nmsi = $lic_or[$nmof]-65;
				if($nmsi<0 || $nmsi>7) return($licb);
				$sidi = 'L';
				$sidj = 'K';
				$cp1 = 5;
				$cp2 = 16;
				for($i=0;$i<$nmsi;$i++)
				{
					$sidl = $lic_or[$cp1]-65;
					if($sidl<0 || $sidl>24) return($licb);
					$sc = $lic_or[$sidl];
					if($sc>=65 && $sc<=74) $sc = $sc-17;
					else $sc = $sc-10;
					$sidi .= chr($sc);
					$cp1+=2;
					$sidl = $lic_or[$cp2]-65;
					if($sidl<0 || $sidl>24) return($licb);
					$sc = $lic_or[$sidl];
					if($sc>=81 && $sc<=90) $sc = $sc-33;
					$sidj .= chr($sc);
					$cp2+=2;
				}
				$sidi = intval(substr($sidi,1),26);
				$sidj = intval(substr($sidj,1),26);
				$lcof = $lic_or[2]-65;
				if($lcof<0 || $lcof>24) return($licb);
				$lcty = $lic_or[$lcof]-67;
				$lcog = $lic_or[15]-65;
				if($lcog<0 || $lcog>24) return($licb);
				$lctg = $lic_or[$lcog]-70;
				if($sidi != $sidj) return($licb);
				if($lcty != $lctg) return($licb);
				$ing = $lic_or[4]-65;
				if($ing<0 || $ing>22) return($licb);
				$ino = $lic_or[24]-65;
				if($ino<0 || $ino>24) return($licb);
				$inb = $lic_or[$ino];
				$ina = '';
				foreach($lic_or as $a=>$v)
				{
					if($a!=24 && $a!=$ino) $ina = $ina.chr($v);
				}
				$inc = ord(substr(strtoupper(md5($ina)),$ing,1));
				if($inc<65 || $inc>90) $inc+=21;
				if($inc!=$inb) return($licb);
				$licb['type'] = $lcty;
				$licb['sit'.'eid'] = $sidi;
			}
			return($licb);
		}
}
class GetTemplate
{
	function GetTemplate($temp,$params)
	{
		if(is_file($temp)) $temp = file_get_contents($temp);
		$this->temp = $temp;
		$this->params = $params;
	}
	function Get($t = false)
	{
		if($t===false) $t = $this->temp;
		$RV = preg_replace_callback('/<%value\s+(.*)%>/ismU',array(&$this,'_GetTemplate_callback'), $t);
		return($RV);
	}
	function GetPart($part)
	{
		if(preg_match('/<%\s*sec-start\s+'.$part.'\s*%>[\n\r]{0,1}(.*)[\n\r]{0,1}<%\s*sec-end\s+'.$part.'\s*%>/ismU',$this->temp,$m)) return($this->Get($m[1]));
		else return('');
	}
	function _GetTemplate_callback($m)
	{
		$id = $m[1];
		if(isset($this->params[$id])) return($this->params[$id]);
		else return('');
	}
}
function InputFilter($s,$allowed)
{
	$RV = '';
	$STL = strlen($s);
	for($i=0;$i<$STL;$i++)
	{
		$CH = substr($s,$i,1);
		if(strpos($allowed,$CH)!==false)
		{
			$RV = $RV . $CH;
		}
	}
	return($RV);
}
function ImageResize($file, $out_file, $width = 0, $height = 0, $proportional = false)
{
	if ( $height <= 0 && $width <= 0 )
	{
		return false;
	}
	$info = getimagesize($file);
	$image = '';
	if(!$info) return false;
	$final_width = 0;
	$final_height = 0;
	list($width_old, $height_old) = $info;
	if($width_old==0 || $height_old==0) return false;
	if ($proportional)
	{
		if ($width == 0) $factor = $height/$height_old;
		elseif ($height == 0) $factor = $width/$width_old;
		else $factor = min ( $width / $width_old, $height / $height_old);
		$final_width = round ($width_old * $factor);
		$final_height = round ($height_old * $factor);
	}
	else
	{
		$final_width = ( $width <= 0 ) ? $width_old : $width;
		$final_height = ( $height <= 0 ) ? $height_old : $height;
	}
	switch ( $info[2] )
	{
		case IMAGETYPE_GIF: $image = imagecreatefromgif($file);
		break;
		case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($file);
		break;
		case IMAGETYPE_PNG: $image = imagecreatefrompng($file);
		break;
		default: return false;
	}
	$image_resized = imagecreatetruecolor( $final_width, $final_height );
	$image_resized2 = imagecreatetruecolor( $width, $height );
	if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) )
	{
		$trnprt_indx = imagecolortransparent($image);
		if ($trnprt_indx >= 0)
		{
			$trnprt_color = imagecolorsforindex($image, $trnprt_indx);
			$trnprt_indx = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
			imagefill($image_resized, 0, 0, $trnprt_indx);
			imagefill($image_resized2, 0, 0, $trnprt_indx);
			imagecolortransparent($image_resized, $trnprt_indx);
			imagecolortransparent($image_resized2, $trnprt_indx);
		}
	}
	imagealphablending($image_resized, false);
	imagealphablending($image_resized2, false);
	$color = imagecolorallocatealpha($image_resized, 255, 255, 255, 127);
	$color2 = imagecolorallocatealpha($image_resized2, 255, 255, 255, 127);
	imagefill($image_resized, 0, 0, $color);
	imagefill($image_resized2, 0, 0, $color2);
	imagesavealpha($image_resized, true);
	imagesavealpha($image_resized2, true);
	imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
	if($final_width == $width)
	{
		$dst_x = 0;
		$dst_y = ($height/2) - ($final_height/2);
	}
	else
	{
		$dst_x = ($width/2) - ($final_width/2);
		$dst_y = 0;
	}
	imagecopyresampled($image_resized2, $image_resized, $dst_x, $dst_y, 0, 0,$final_width,$final_height, $final_width, $final_height);
	if($out_file===false)
	{
		header('Content-Type: image/png');
		imagepng($image_resized2);
	}
	else
	{
		imagepng($image_resized2, $out_file);
	}
	return true;
}
function URL2View($s)
{
	$sp = parse_url($s);
	$RV = '';
	if(!isset($sp['host'])) $sp['host'] = '';
	if(substr($sp['host'],0,4)=='www.') $sp['host'] = substr($sp['host'],4);
	if(strlen($sp['host'])>10) $sp['host'] = substr($sp['host'],0,7).'...';
	if(strlen($sp['path'])>30) $sp['path'] = '/'.substr($sp['path'],-27);
	$RV .= $sp['host'].$sp['path'];
	$qu = ArrayMember($sp,'query','');
	if(strlen($qu)>0)
	{
		$RV .= '?'.$qu;
	}
	return($RV);
}
function GetAdminPanelJavaScriptFiles()
{
	$files = array (
		'yahoo.js'=>false, 
		'ajax_request.js'=>true, 
		'flash.js'=>true, 
		'md5.js'=>true, 
		'_base.js'=>true, 
		'_base_core.js'=>true, 
		'_base_transfer.js'=>true, 
		'_base_config.js'=>true, 
		'_base_dialog_agents.js'=>true, 
		'_base_dialog_departments.js'=>true, 
		'_base_dialog_fields.js'=>true, 
		'_base_dialog_blocked.js'=>true, 
		'_base_dialog_news.js'=>true, 
		'_base_news.js'=>true, 
		'_base_reports.js'=>true, 
		'_base_visitors.js'=>true, 
		'_base_home.js'=>true 
	);
	if(LIVEADMIN_STANDALONE)
	{
		$files['_base_upgrade.js'] = true;
	}
	return($files);
}
function GetAdminPanelJavaScriptVersion()
{
	$files = GetAdminPanelJavaScriptFiles();
	$m=0;
	if(LIVEADMIN_STANDALONE) $bdir = LIVEADMIN_AST;
	foreach($files as $file=>$strip)
	{
		$m += filemtime($bdir.'/'.$file);
	}
	return(md5($m));
}
function GetAdminPanelStyleFiles($dir)
{
	$dir = InputFilter($dir,LIVEADMIN_CON_L.LIVEADMIN_CON_U);
	$files = array ( 'yahoo.css'=>false, 'style.css'=>true, 'style_'.$dir.'.css'=>true );
	return($files);
}
function GetAdminPanelStyleVersion($dir)
{
	$files = GetAdminPanelStyleFiles($dir);
	$m=0;
	if(LIVEADMIN_STANDALONE) $bdir = LIVEADMIN_AST;
	foreach($files as $file=>$strip)
	{
		if(is_file($bdir.'/'.$file)) $m += filemtime($bdir.'/'.$file);
	}
	return(md5($m));
}
function GetVisitorCallback()
{
	$ip = $_SERVER['REMOTE_ADDR'];
	$RV = 0;
	$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
	mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
	$tbl = LIVEADMIN_DB_PREFIX.'visitors_trig';
	$ip_esc = mysql_real_escape_string($ip,$dbh);
	$res = mysql_query("SELECT * FROM $tbl WHERE ip='$ip_esc' ",$dbh);
	if(mysql_affected_rows($dbh)>0)
	{
		mysql_query("DELETE FROM $tbl WHERE ip='$ip_esc' ",$dbh);
		$RV = 1;
	}
	mysql_close($dbh);
	return $RV;
}
function Lic2Text($s)
{
	return(implode("-",str_split($s,5)));
}
function lv_sort_news_feeds_callback($a,$b)
{
	if($a['type']=='st' && $a['sticky']=='y')
	{
		if($b['type']!='st' || $b['sticky']=='n') return(-1);
	}
	if($b['type']=='st' && $b['sticky']=='y')
	{
		if($a['type']!='st' || $a['sticky']=='n') return(1);
	}
	if($a['date']>$b['date']) return(-1);
	elseif($b['date']>$a['date']) return(1);
	return(0);
}
?>