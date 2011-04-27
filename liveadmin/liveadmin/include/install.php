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
ini_set('implicit_flush',true);
ini_set('display_errors',false);
include_once('mysqldump_import.php');
class LV_LiveAdminInstall
{
	function Run()
	{
		if(!isset($_REQUEST['step'])) $_REQUEST['step'] = '1_0';
		if($this->IsInstalled() && $_REQUEST['step']!='7_0' && $_REQUEST['step']!='7_5')
		{
			$params = array();
			$params['cyear'] = date('Y');
			$mt = new GetTemplate('include/install_page.php',$params);
			$page = $mt->GetPart('header');
			$page .= $mt->GetPart('step_100');
			$page .= $mt->GetPart('footer');
			print $page;
			return;
		}
		switch($_REQUEST['step'])
		{
			case '1_0': $params = array();
			$params['cyear'] = date('Y');
			$mt = new GetTemplate('include/install_page.php',$params);
			$page = $mt->GetPart('header');
			$page .= $mt->GetPart('step_001');
			$page .= $mt->GetPart('footer');
			print $page;
			break;
			case '1_5': header('Location: ?act=install&step=2_0');
			exit;
			case '2_0': $params = array();
			$params['cyear'] = date('Y');
			$params['system_req_info'] = '';
			$sys_req = $this->GetSystemReq();
			foreach($sys_req as $a=>$v)
			{
				$in = 'p';
				if($v['status']==0) $in = 'em';
				$params['system_req_info'] .= '<span>'.$a.'</span><'.$in.'>'.$v['info'].'</'.$in.'><div class="clear"></div>';
			}
			$mt = new GetTemplate('include/install_page.php',$params);
			$page = $mt->GetPart('header');
			$page .= $mt->GetPart('step_002');
			$page .= $mt->GetPart('footer');
			print $page;
			break;
			case '2_5': header('Location: ?act=install&step=3_0');
			exit;
			case '3_0': if($this->IsConfigWritable())
			{
				header('Location: ?act=install&step=4_0');
				exit;
			}
			$params = array();
			$params['cyear'] = date('Y');
			$mt = new GetTemplate('include/install_page.php',$params);
			$page = $mt->GetPart('header');
			$page .= $mt->GetPart('step_003');
			$page .= $mt->GetPart('footer');
			print $page;
			break;
			case '3_5': header('Location: ?act=install&step=3_0');
			exit;
			case '4_0': $params = array();
			$params['cyear'] = date('Y');
			$params['lv_db_host'] = 'localhost';
			$params['lv_db_prefix'] = 'lvad_';
			$params['lv_path'] = $this->GetPath();
			$params['lv_url'] = $this->GetURL();
			$mt = new GetTemplate('include/install_page.php',$params);
			$page = $mt->GetPart('header');
			$page .= $mt->GetPart('step_004');
			$page .= $mt->GetPart('footer');
			print $page;
			break;
			case '4_5': $params = array();
			$params['cyear'] = date('Y');
			$params['lv_db_host'] = $_REQUEST['lv_db_host'];
			$params['lv_db_name'] = $_REQUEST['lv_db_name'];
			$params['lv_db_user'] = $_REQUEST['lv_db_user'];
			$params['lv_db_pass'] = $_REQUEST['lv_db_pass'];
			$params['lv_db_prefix'] = $_REQUEST['lv_db_prefix'];
			$params['lv_path'] = $_REQUEST['lv_path'];
			$params['lv_url'] = $_REQUEST['lv_url'];
			if(($err = $this->CheckDatabaseConnection($_REQUEST['lv_db_host'],$_REQUEST['lv_db_name'],$_REQUEST['lv_db_user'],$_REQUEST['lv_db_pass'],$_REQUEST['lv_db_prefix']))!='')
			{
				$params['error_text'] = $err;
				$mt = new GetTemplate('include/install_page.php',$params);
				$params['error'] = $mt->GetPart('error');
				$mt = new GetTemplate('include/install_page.php',$params);
				$page = $mt->GetPart('header');
				$page .= $mt->GetPart('step_004');
				$page .= $mt->GetPart('footer');
				print $page;
			}
			elseif(!$this->CheckPath($_REQUEST['lv_path']))
			{
				$params['error_text'] = 'Absolute path is not exists or not contain LiveAdmin installation';
				$mt = new GetTemplate('include/install_page.php',$params);
				$params['error'] = $mt->GetPart('error');
				$mt = new GetTemplate('include/install_page.php',$params);
				$page = $mt->GetPart('header');
				$page .= $mt->GetPart('step_004');
				$page .= $mt->GetPart('footer');
				print $page;
			}
			elseif(!$this->WriteConfigFile())
			{
				$params['error_text'] = 'Unable to write to include/config.php file, Please make sure this file is writable by PHP process';
				$mt = new GetTemplate('include/install_page.php',$params);
				$params['error'] = $mt->GetPart('error');
				$mt = new GetTemplate('include/install_page.php',$params);
				$page = $mt->GetPart('header');
				$page .= $mt->GetPart('step_004');
				$page .= $mt->GetPart('footer');
				print $page;
			}
			else
			{
				header('Location: ?act=install&step=5_0');
				exit;
			}
			break;
			case '5_0': if(!$this->IsConfigWritable())
			{
				header('Location: ?act=install&step=6_0');
				exit;
			}
			$params = array();
			$params['cyear'] = date('Y');
			$mt = new GetTemplate('include/install_page.php',$params);
			$page = $mt->GetPart('header');
			$page .= $mt->GetPart('step_005');
			$page .= $mt->GetPart('footer');
			print $page;
			break;
			case '5_5': header('Location: ?act=install&step=5_0');
			exit;
			case '5_7': header('Location: ?act=install&step=6_0');
			exit;
			case '6_0': $params = array();
			$params['cyear'] = date('Y');
			$mt = new GetTemplate('include/install_page.php',$params);
			$page = $mt->GetPart('header');
			$page .= $mt->GetPart('step_006');
			$page .= $mt->GetPart('footer');
			print $page;
			break;
			case '6_5': $params = $_REQUEST;
			$err = '';
			if(($err = $this->CheckSiteInfo($_REQUEST))!='')
			{
				$params['error_text'] = $err;
				$mt = new GetTemplate('include/install_page.php',$params);
				$params['error'] = $mt->GetPart('error');
				$mt = new GetTemplate('include/install_page.php',$params);
				$page = $mt->GetPart('header');
				$page .= $mt->GetPart('step_006');
				$page .= $mt->GetPart('footer');
				print $page;
			}
			elseif(($err = $this->InstallDatabases($_REQUEST))!='')
			{
				$params['error_text'] = $err;
				$mt = new GetTemplate('include/install_page.php',$params);
				$params['error'] = $mt->GetPart('error');
				$mt = new GetTemplate('include/install_page.php',$params);
				$page = $mt->GetPart('header');
				$page .= $mt->GetPart('step_006');
				$page .= $mt->GetPart('footer');
				print $page;
			}
			else
			{
				header('Location: ?act=install&step=7_0');
				exit;
			}
			break;
			case '7_0': $params = array();
			$params['cyear'] = date('Y');
			$params['login_address'] = LIVEADMIN_W.'/';
			$mt = new GetTemplate('include/install_page.php',$params);
			$page = $mt->GetPart('header');
			$page .= $mt->GetPart('step_007');
			$page .= $mt->GetPart('footer');
			print $page;
			break;
			case '7_5': header('Location: '.LIVEADMIN_W.'/');
			exit;
		}
	}
	function GetSystemReq()
	{
		$RV = array();
		$php_version_i = phpversion();
		$php_version_r = '5.1.0';
		if(version_compare($php_version_i,$php_version_r,"<")) $RV['PHP Version'] = array('info'=>'Required '.$php_version_r.' or later - installed '.$php_version_i,'status'=>0);
		else $RV['PHP Version'] = array('info'=>'Required '.$php_version_r.' or later - installed '.$php_version_i,'status'=>1);
		if(!is_callable('mysql_connect')) $RV['PHP MySQL Ext.'] = array('info'=>'PHP does not compiled with MySQL extension','status'=>0);
		else $RV['PHP MySQL Ext.'] = array('info'=>'PHP has MySQL extension','status'=>1);
		$mysql_client = mysql_get_client_info();
		$mysql_client_info = explode(".",$mysql_client);
		if(count($mysql_client_info)==0 || !is_numeric($mysql_client_info[0])) $RV['MySQL Client'] = array('info'=>'Unable to get MySQL client version','status'=>0);
		elseif($mysql_client_info[0]<4) $RV['MySQL Client'] = array('info'=>'Required MySQL Client 4.x or later - installed '.$mysql_client,'status'=>0);
		else $RV['MySQL Client'] = array('info'=>'Required MySQL Client 4.x or later - installed '.$mysql_client,'status'=>1);
		if(!is_callable('utf8_encode')) $RV['UTF Encoder'] = array('info'=>'PHP does not compiled with UTF8 Encoder','status'=>0);
		else $RV['UTF Encoder'] = array('info'=>'PHP has UTF8 Encoder','status'=>1);
		if(!is_callable('ftp_connect')) $RV['FTP Functions'] = array('info'=>'PHP does not compiled with FTP functions','status'=>0);
		else $RV['FTP Functions'] = array('info'=>'PHP has FTP functions','status'=>1);
		$register_globals = (int) ini_get('register_globals');
		if($register_globals) $RV['Register Globals'] = array('info'=>'register_globals is ON, it should be OFF for security','status'=>0);
		else $RV['Register Globals'] = array('info'=>'register_globals in OFF','status'=>1);
		$safe_mode = (int) ini_get('safe_mode');
		if($safe_mode) $RV['PHP Safe Mode'] = array('info'=>'PHP is running in safe_mode, LiveAdmin has not tested under safe_mode','status'=>0);
		else $RV['PHP Safe Mode'] = array('info'=>'PHP is not running in safe_mode','status'=>1);
		if(!is_callable('curl_init')) $RV['CURL Functions'] = array('info'=>'PHP does not compiled with CURL extension','status'=>0);
		else
		{
			$RV['CURL Functions'] = array('info'=>'PHP has CURL functions','status'=>1);
			if(!$this->IsCurlPossible()) $RV['TCP Out connection'] = array('info'=>'CURL can not connect to outside world, either this server is not connected to internet or a firewall blocks the outgoing TCP connections on port 80 to www.liveadmin.net','status'=>0);
			else $RV['TCP Out connection'] = array('info'=>'CURL can connect to www.liveadmin.net','status'=>1);
		}
		return($RV);
	}
	function CheckDatabaseConnection($host,$name,$user,$pass,&$prefix)
	{
		$prefix = trim($prefix);
		if(strlen($prefix)<2) return('Table prefix should be at least 2 characters long.');
		$dbh=mysql_connect($host, $user,$pass,true);
		if (!$dbh) return('Unable to connect to MySQL database using provided information');
		if(!mysql_select_db($name,$dbh))
		{
			mysql_close($dbh);
			return('I can connect to MySQL server, but there is no database named '.$name);
		}
		$installed_found = false;
		$tbl = $prefix.'sites';
		$res = mysql_query("SELECT * FROM $tbl",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$installed_found = true;
				break;
			}
		}
		$tbl = $prefix.'users';
		$res = mysql_query("SELECT * FROM $tbl",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$installed_found = true;
				break;
			}
		}
		mysql_close($dbh);
		if($installed_found) return('A LiveAdmin database has already installed on '.$name.' with table prefix '.$prefix);
		return('');
	}
	function IsConfigWritable()
	{
		if(is_file('include/config.php') && is_writable('include/config.php')) return(true);
		return(false);
	}
	function CheckPath(&$path)
	{
		$path = preg_replace('/\/$/','',$path);
		if(!is_dir($path)) return false;
		if(!is_file($path.'/include/admin.php')) return false;
		if(!is_file($path.'/assets/_base.js')) return false;
		return(true);
	}
	function WriteConfigFile()
	{
		if(!$this->IsConfigWritable()) return(false);
		$config_file = 'include/config.php';
		$cf = '<%sec-start main%>'.file_get_contents($config_file).'<%sec-end main%>';
		$params = array();
		$params['lv_db_host'] = $_REQUEST['lv_db_host'];
		$params['lv_db_name'] = $_REQUEST['lv_db_name'];
		$params['lv_db_user'] = $_REQUEST['lv_db_user'];
		$params['lv_db_pass'] = $_REQUEST['lv_db_pass'];
		$params['lv_db_prefix'] = $_REQUEST['lv_db_prefix'];
		$params['web_address'] = $_REQUEST['lv_url'];
		$params['absolute_path'] = $_REQUEST['lv_path'];
		$params['liveadmin_installed'] = 'YES';
		$params['lv_uniq'] = RandomString(64);
		$mt = new GetTemplate($cf,$params);
		$cp = $mt->GetPart('main');
		file_put_contents($config_file,$cp);
		return(true);
	}
	function GetPath()
	{
		$path = preg_replace('/\/include$/','',dirname(__FILE__));
		$path = preg_replace('/\/$/','',$path);
		return($path);
	}
	function GetURL()
	{
		$sname = $_SERVER['HTTP_HOST'];
		$sport = $_SERVER['SERVER_PORT'];
		$pself = $_SERVER['PHP_SELF'];;
		$pself = preg_replace('/index\.php$/','',$pself);
		$pself = preg_replace('/\/$/','',$pself);
		if($pself=='') $pself = '/';
		if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])=='on')
		{
			if($sport==443) $sport = '';
			else $sport = ':'.$sport;
			return('https://'.$sname.$sport.$pself);
		}
		else
		{
			if($sport==80) $sport = '';
			else $sport = ':'.$sport;
			return('http://'.$sname.$sport.$pself);
		}
	}
	function InstallDatabases($r)
	{
		$db_dir = LIVEADMIN_FQ;
		if(is_dir($db_dir))
		{
			if ($dh = opendir($db_dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if($file=='.' || $file == '..') continue;
					if(is_file($db_dir.'/'.$file)) $FList[] = $db_dir.'/'.$file;
				}
				closedir($dh);
			}
		}
		$mysql = new MySQLDumpImport();
		sort($FList);
		$params = $r;
		$params['table_prefix']=LIVEADMIN_DB_PREFIX;
		$params['siteid']=rand(1000,456000);
		$params['userid']=17540;
		$params['lv_theme']='n3_yellow';
		$params['lv_signup_date']=time();
		$params['lv_password']=crypt($params['lv_password'],'$1$'.RandomString(9));
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('Unable to connect to database');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		foreach($params as $a=>$v)
		{
			$params[$a] = mysql_real_escape_string($v,$dbh);
		}
		mysql_close($dbh);
		foreach($FList as $file)
		{
			$fname = basename($file);
			if(substr($fname,-4)!='.sql' || substr($fname,0,12)!='db_dump_000_') continue;
			$RV[] = "Importing $file";
			$dump = "";
			$tmpl = new GetTemplate($file,$params);
			if($force) $dump .= $tmpl->GetPart('drop')."\n";
			$dump .= $tmpl->GetPart('table')."\n";
			$dump .= $tmpl->GetPart('data')."\n";
			$dump .= $tmpl->GetPart('stl_data')."\n";
			$mysql->importDumpString($dump);
			if(count($mysql->Error)>0)
			{
				foreach($mysql->Error as $v) $RV[] = $v;
			}
			$mysql->Error = array();
		}
		unset($mysql);
		return('');
	}
	function CheckSiteInfo(&$r)
	{
		foreach($r as $a=>$v)
		{
			$r[$a] = trim($v);
		}
		if($r['lv_company']=='') return('Name of the company or web site can not be empty.');
		if(!IsValidEmailSyntax($r['lv_general_email'])) return('General email should be a valid email address');
		if(strlen($r['lv_firstname'])<2) return('First name should be at least 2 characters');
		if(strlen($r['lv_lastname'])<2) return('Last name should be at least 2 characters');
		if(strlen($r['lv_username'])<5 || strlen($r['lv_username'])>50 || InputFilter($r['lv_username'],LIVEADMIN_CON_D.LIVEADMIN_CON_L.'_')!=$r['lv_username']) return('Username should be alpha numeric and between 5 to 50 characters');
		if(strlen($r['lv_password'])<5 || strlen($r['lv_password'])>50) return('Password should be between 5 to 50 characters');
		if(!IsValidEmailSyntax($r['lv_email'])) return('Personal email should be a valid email address');
		$installed_found = false;
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('Unable to connect to database');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'sites';
		$res = mysql_query("SELECT * FROM $tbl",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$installed_found = true;
				break;
			}
		}
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$res = mysql_query("SELECT * FROM $tbl",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$installed_found = true;
				break;
			}
		}
		mysql_close($dbh);
		if($installed_found) return('Some tables are not empty, it seems LiveAdmin has already installed, for your protection I can not continue this installation unless you drop all tables in '.LIVEADMIN_DB_DATABASE.' database that start with '.LIVEADMIN_DB_PREFIX);
		return('');
	}
	function IsInstalled()
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true);
		if (!$dbh) return(false);
		if(!mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh))
		{
			mysql_close($dbh);
			return(false);
		}
		$installed_found = false;
		$tbl = LIVEADMIN_DB_PREFIX.'sites';
		$res = mysql_query("SELECT * FROM $tbl",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$installed_found = true;
				break;
			}
		}
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$res = mysql_query("SELECT * FROM $tbl",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$installed_found = true;
				break;
			}
		}
		mysql_close($dbh);
		if ($installed_found) return(true);
		return false;
	}
	function IsCurlPossible()
	{
		$header[] = "Accept: text/vnd.wap.wml,*.*";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.liveadmin.net/robots.txt");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		@curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_VERBOSE,0);
		curl_setopt($ch, CURLOPT_USERAGENT,'LiveAdmin');
		curl_setopt($ch, CURLOPT_POST,0);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch, CURLOPT_TIMEOUT,30);
		$data = curl_exec ($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		if(strpos(strtolower($data),'user-agent')!==false)
		{
			return true;
		}
		return false;
	}
}
?>