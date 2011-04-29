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
include_once('lang.php');
class LV_Client
{
	var $SInfo;
	var $ValidSite;
	var $lang;
	function LV_Client($site_id_in)
	{
		$this->lang = null;
		$this->SInfo = GetSiteInfo('siteid',$site_id_in);
		$this->ValidSite = IsValid_SInfo($this->SInfo);
		if($this->ValidSite!==true)
		{
			$this->SInfo = array();
			$this->SInfo['online_status'] = -1;
			$this->SInfo['offline_act'] = 1;
		}
		else
		{
			$this->SInfo['key'] = ID2Key($this->SInfo['siteid']);
			if($this->SInfo['last_act']>time()-(60)) $this->SInfo['online_status'] = 1;
			else $this->SInfo['online_status'] = 0;
			$this->ModLite();
		}
	}
	function Run()
	{
		if(!isset($_REQUEST['mode']))
			$_REQUEST['mode'] = '';
		if($this->ValidSite!==true && $_REQUEST['mode']!='base')
		{
			include_once('jspack.php');
			$this->DefaultProcessorInvalid($this->ValidSite);
		}
		else
		{
			switch($_REQUEST['mode'])
			{
				default:
					include_once('jspack.php');
					$this->DefaultProcessor();
				break;
				case 'base':
					include_once('jspack.php');
					$this->BaseProcessor();
				break;
				case 'chat':
					include_once('jspack.php');
					if(isset($_REQUEST['compatible']) && $_REQUEST['compatible']==1)
					{
						$this->SInfo['theme'] = 'popup_basic';
					}
					$this->lang = new LV_Lang($this->SInfo['language']);
					$this->lang->MergeWithInfo(unserialize($this->SInfo['chat_window_texts']));
					$page = $this->LoadTheme();
					$page = $this->TranslateTheme($page);
					$page = $this->lang->Translate($page);
					$page = $this->TranslateTheme($page);
					$this->SetClientCookie();
					$page = $this->OptimizePage($page);
					$this->ShowPage($page);
					if(!LIVEADMIN_STANDALONE)
					{
						include_once('s3aws.php');
						$s3aws = new LV_S3AWS();
						$s3aws->CleanChat($_SERVER['REMOTE_ADDR']);
						unset($s3aws);
					}
				break;
				case 'clean_callback':
					if(!LIVEADMIN_STANDALONE)
					{
						include_once('s3aws.php');
						$s3aws = new LV_S3AWS();
						$s3aws->CleanChat($_SERVER['REMOTE_ADDR']);
						unset($s3aws);
					}
				break;
				case 'close':
					$this->CloseChat($_REQUEST['client_uniq']);
				break;
				case 'message':
					$this->PostNewMessage($_REQUEST['message'],$_REQUEST['client_uniq'],$_REQUEST['server_uniq']);
				break;
				case 'control':
					$this->PostNewControlMessage($_REQUEST['message'],$_REQUEST['client_uniq'],$_REQUEST['server_uniq']);
				break;
				case 'message_loop':
					$mloop = $this->GetMessageLoop($_REQUEST['client_uniq'],$_REQUEST['server_uniq'],substr($_REQUEST['last_check'],2)*1);
					print AjaxPack($mloop);
				break;
				case 'init':
					if($this->CheckBlockAccess($_REQUEST['key']))
					{
						print AjaxPack(array('init_status'=>1,'user_blocked'=>'yes'));
					}
					else
					{
						$this->AddClientUniq($_REQUEST['client_uniq'],$_REQUEST['key']);
						print AjaxPack(array('init_status'=>1,'user_blocked'=>'no'));
					}
				break;
				case 'post_user_info':
					$this->PostUserInfo($_REQUEST['client_uniq'],$_REQUEST['key'],$_REQUEST);
				break;
				case 'post_take_message':
					$this->UpdateClientFlag($_REQUEST['client_uniq'],$_REQUEST['key'],3);
					$this->PostTakeMessage($_REQUEST['client_uniq'],$_REQUEST['key'],$_REQUEST);
				break;
				case 'pic':
					$this->AgentPic($_REQUEST['id'],$_REQUEST['size']);
				break;
				case 'find_rep':
					$res = $this->CheckWait($_REQUEST['client_uniq']);
					if(isset($res['server_uniq']) && $res['server_uniq']!='')
					{
						$agent_pic = ($this->GetAgentPic($res['server_userid']))?'on':'off';
						$this->UpdateClientFlag($_REQUEST['client_uniq'],$_REQUEST['key'],4);
						print AjaxPack(array('find_status'=>1,'server_uniq'=>$res['server_uniq'],'server_nickname'=>$res['server_nickname'],'server_userid'=>$res['server_userid'],'server_pic'=>$agent_pic,'last_check'=>'LC0'));
					}
					elseif($res['client_flag']==2)
					{
						print AjaxPack(array('find_status'=>2));
					}
					elseif($res['client_flag']==5)
					{
						print AjaxPack(array('find_status'=>3));
					}
					elseif(intval(RealTime())-intval($res['client_dtm'])>$this->SInfo['wait_find_rep_s'])
					{
						switch($this->SInfo['no_answer_act'])
						{
							case 0:
								$this->UpdateClientFlag($_REQUEST['client_uniq'],$_REQUEST['key'],2);
								print AjaxPack(array('find_status'=>2));
							break;
							case 1:
								$this->UpdateClientFlag($_REQUEST['client_uniq'],$_REQUEST['key'],5);
								print AjaxPack(array('find_status'=>3));
							break;
						}
					}
					else
					{
						print AjaxPack(array('find_status'=>0));
					}
				break;
				case 'update_flag':
					$this->UpdateClientFlag($_REQUEST['client_uniq'],$_REQUEST['key'],$_REQUEST['flag']);
					print AjaxPack(array('status'=>1));
				break;
			}
		}
	}
	function OptimizePage($page)
	{
		$out = $page;
		$out = preg_replace('/\r\n/',"\n",$out);
		$out = preg_replace('/\n[\s\t]*/','',$out);
		$out = preg_replace('/<!--.*-->/ismU','',$out);
		return($out);
	}
	function LoadTheme($file = 'liveadmin_chat.htm')
	{
		if(!isset($this->SInfo['theme_setting']))
			$this->GetThemeSetting();
		$RV = '';
		if(is_file(LIVEADMIN_T.'/'.$this->SInfo['theme'].'/'.$file))
		{
			$RV = file_get_contents(LIVEADMIN_T.'/'.$this->SInfo['theme'].'/'.$file);
		}
		elseif(ArrayMember($this->SInfo['theme_setting'],$file.'_default')!='')
		{
			$RV = file_get_contents(LIVEADMIN_T.'/'.ArrayMember($this->SInfo['theme_setting'],$file.'_default').'/'.$file);
		}
		else
		{
			$RV = file_get_contents(LIVEADMIN_T.'/'.LIVEADMIN_DEFAULT_THEME.'/'.$file);
		}
		return($RV);
	}
	function LoadThemeFileName($file)
	{
		if(!isset($this->SInfo['theme_setting'])) $this->GetThemeSetting();
		$RV = '';
		if(is_file(LIVEADMIN_T.'/'.$this->SInfo['theme'].'/'.$file))
		{
			$RV = $this->SInfo['theme'].'/'.$file;
		}
		elseif(ArrayMember($this->SInfo['theme_setting'],$file.'_default')!='')
		{
			$RV = ArrayMember($this->SInfo['theme_setting'],$file.'_default').'/'.$file;
		}
		else
		{
			$RV = LIVEADMIN_DEFAULT_THEME.'/'.$file;
		}
		return($RV);
	}
	function LoadThemeFileMulti($file)
	{
		if(!isset($this->SInfo['theme_setting'])) $this->GetThemeSetting();
		$RV_DEF = '';
		$RV_THM = '';
		if($this->SInfo['theme']!=LIVEADMIN_DEFAULT_THEME && is_file(LIVEADMIN_T.'/'.$this->SInfo['theme'].'/'.$file))
		{
			$RV_THM = file_get_contents(LIVEADMIN_T.'/'.$this->SInfo['theme'].'/'.$file);
		}
		if(isset($this->SInfo['theme_setting'][$file.'_no_default_include']) && $this->SInfo['theme_setting'][$file.'_no_default_include'])
		{
			return($RV_THM);
		}
		if(isset($this->SInfo['theme_setting'][$file.'_include']))
		{
			if(!is_array($this->SInfo['theme_setting'][$file.'_include']))
			{
				$this->SInfo['theme_setting'][$file.'_include'] = array($this->SInfo['theme_setting'][$file.'_include']);
			}
			$RV_DEF_INC = '';
			foreach($this->SInfo['theme_setting'][$file.'_include'] as $a=>$v)
			{
				if(is_file(LIVEADMIN_T.'/'.$v.'/'.$file))
				{
					$RV_DEF_INC .= file_get_contents(LIVEADMIN_T.'/'.$v.'/'.$file)."\n";
				}
			}
			return($RV_DEF_INC.$RV_THM);
		}
		if(is_file(LIVEADMIN_T.'/'.LIVEADMIN_DEFAULT_THEME.'/'.$file))
		{
			$RV_DEF = file_get_contents(LIVEADMIN_T.'/'.LIVEADMIN_DEFAULT_THEME.'/'.$file);
		}
		return($RV_DEF."\n".$RV_THM);
	}
	function TranslateTheme($page)
	{
		if(is_array($page))
		{
			foreach($page as $a=>$v)
			{
				$page[$a] = preg_replace_callback('/<%value\s+(.*)%>/ismU',array(&$this,'_translate_theme_callback'), $v);
			}
			return($page);
		}
		else
		{
			return(preg_replace_callback('/<%value\s+(.*)%>/ismU',array(&$this,'_translate_theme_callback'), $page));
		}
	}
	function _translate_theme_callback($m)
	{
		$id = $m[1];
		if(substr($id,0,6)=='sinfo_')
		{
			return($this->SInfo[substr($id,6)]);
		}
		elseif(substr($id,0,10)=='site_info_')
		{
			return($this->SInfo[substr($id,10)]);
		}
		elseif($id=='theme_folder_w')
		{
			return(LIVEADMIN_WT.'/'.$this->SInfo['theme'].'/');
		}
		elseif($id=='W')
		{
			return(500);
		}
		elseif($id=='H')
		{
			return(350-30);
		}
		elseif(substr($id,0,16)=='theme_folder_ws_')
		{
			$f = substr($id,16);
			if(is_file(LIVEADMIN_T.'/'.$this->SInfo['theme'].'/'.$f)) return(LIVEADMIN_WT.'/'.$this->SInfo['theme'].'/'.$f);
			else return(LIVEADMIN_WT.'/'.LIVEADMIN_DEFAULT_THEME.'/'.$f);
		}
		elseif($id=='java_script')
		{
			$texts = $this->lang->GetAllStrings();
			$texts = liveadmin_implode($texts,'texts_','',':','"','"',',');
			$texts = $this->TranslateTheme($texts);
			$fields = $this->SInfo['extra_fields'];
			$fields = ($fields=='')?array():unserialize($fields);
			$fields_ar = array();
			$departments = lv_get_departments($this->SInfo);
			if(count($departments)>0)
			{
				$fields_ar[] = '"_lv_department"';
			}
			foreach($fields as $a=>$v)
			{
				$fields_ar[] = '"'.$v['name'].'"';
			}
			$fields_txt = implode(",",$fields_ar);
			$client_uniq = (isset($_REQUEST['client_uniq']))?$_REQUEST['client_uniq']:RandomString(32);
			$js ='';
			$js .= 'var LiveAdmin = {';
			$js .= 'site_key:"'.$this->SInfo['key'].'",';
			$js .= 'client_uniq:"'.$client_uniq.'",';
			$js .= 'server_uniq:"",';
			$js .= 'client_nickname:"",';
			$js .= 'server_nickname:"",';
			$js .= 'flash_install:'.$this->SInfo['flash_install'].',';
			$js .= 'last_check_msg:"",';
			$js .= 'trig_find_rep:0,';
			if(!isset($this->SInfo['theme_setting'])) $this->GetThemeSetting();
			foreach($this->SInfo['theme_setting'] as $a=>$v)
			{
				if(substr($a,0,3)=='js_' || substr($a,0,3)=='ja_')
				{
					$a = substr($a,3);
					if(is_numeric($v)) $js .= $a.': '.$v.',';
					else $js .= $a.': "'.$v.'",';
				}
			}
			$js .= 'conf_online_status: '.$this->SInfo['online_status'].',';
			$js .= 'conf_offline_act:'.intval($this->SInfo['offline_act']).',';
			$js .= 'conf_no_answer_act:'.intval($this->SInfo['no_answer_act']).',';
			$js .= 'conf_base_url:"'.LIVEADMIN_WCC.'",';
			$js .= 'conf_chat_url:"'.LIVEADMIN_URL_CHAT.'",';
			$laef = array();
			foreach($_REQUEST as $a=>$v)
			{
				if(substr($a,0,5)=='laef_')
				{
					$laef[substr($a,5)] = $v;
				}
			}
			$js .= 'conf_laef:"'.encode64(serialize($laef)).'",';
			$direct_link = LIVEADMIN_MASTER_URL.'/';
			$affiliate_link = LIVEADMIN_MASTER_URL.'/affiliate.php?id='.$this->SInfo['siteid'];
			if(LIVEADMIN_STANDALONE)
			{
				$direct_link = 'http://www.liveadmin.net/';
				$affiliate_link = 'http://www.liveadmin.net/';
			}
			if(isset($this->SInfo['show_affiliate_link']) && ($this->SInfo['show_affiliate_link']==1 || $this->SInfo['in_trial']==1 || $this->SInfo['demo_mode']==1 ))
			{
				if($this->SInfo['in_trial']==1 || $this->SInfo['demo_mode']==1 || LIVEADMIN_LITE)
					$js .= 'conf_affiliate_link:"'.$direct_link.'",';
				else
					$js .= 'conf_affiliate_link:"'.$affiliate_link.'",';
				$js .= 'conf_show_affiliate_link:"yes",';
			}
			else
			{
				$js .= 'conf_affiliate_link:"'.$affiliate_link.'",';
				$js .= 'conf_show_affiliate_link:"no",';
			}
			foreach($_SERVER['CONTROL_MESSAGES'] as $a=>$v)
			{
				$js .= 'CTL_'.$a.': "'.$v.'",';
			}
			if(isset($_REQUEST['ob']) && $_REQUEST['ob']==1)
				$js .= 'ob: 1,';
			else
				$js .= 'ob: 0,';
			if(isset($_REQUEST['dc']) && $_REQUEST['dc']==1)
				$js .= 'dc: "y",';
			else
				$js .= 'dc: "n",';
			$js .= 'extra_fields:['.$fields_txt.'],';
			if($this->SInfo['sound_new_message']!='--none--')
			{
				$js .= 'conf_soundflash:"'.LIVEADMIN_WSND.'?sound_file_1='.LIVEADMIN_WR.'/'.$this->SInfo['sound_new_message'].'",';
			}
			else
			{
				$js .= 'conf_soundflash:"",';
			}
			if($_REQUEST['client_nickname'])
			{
				$js .= 'client_nickname:"'.$_REQUEST['client_nickname'].'",';
			}
			$js .= 'theme_loadicon: "'.LIVEADMIN_WT.'/'.$this->LoadThemeFileName('loader_01.gif').'",';
			$js .= 'message_div_index: 0';
			$js .= ($texts!='')?',':'';
			$js .= $texts;
			$js .= '};'."\n";
			if(LIVEADMIN_STANDALONE)
			{
				$myPacker = new JavaScriptPacker($js, 'Numeric', true, false);
				$js_packed = $myPacker->pack().";\n";
				$js_packed .= file_get_contents(LIVEADMIN_AST.'/prototype-1.6.0.3.js')."\n";
				$js_packed .= file_get_contents(LIVEADMIN_AST.'/flash.js')."\n";
				$myPacker = new JavaScriptPacker(file_get_contents(LIVEADMIN_AST.'/client_chat-decoded.js'), 'Numeric', true, false);
				$js_packed .= $myPacker->pack().";\n";
				$CustomJS = $this->GetCustomThemeJs();
				if($CustomJS!='')
				{
					$myPacker2 = new JavaScriptPacker($CustomJS, 'Numeric', true, false);
					$js_packed .= $myPacker2->pack().";\n";
				}
			}
			return($js_packed);
		}
		elseif($id=='css')
		{
			$css = '';
			$css .= $this->TranslateTheme($this->LoadThemeFileMulti('liveadmin_chat.css'));
			return($css);
		}
		elseif($id=='custom_css')
		{
			if($this->SInfo['in_trial']==1) return('');
			if($this->SInfo['chat_window_options']!='')
			{
				$cwo = unserialize($this->SInfo['chat_window_options']);
				$custom_css = ArrayMember($cwo,'theme_custom_css','');
				if($custom_css!='')
				{
					return('<link rel="stylesheet" type="text/css" href="'.$custom_css.'" />');
				}
			}
		}
		elseif($id=='fields')
		{
			$RV = '';
			$departments = lv_get_departments($this->SInfo);
			if(count($departments)>0)
			{
				$opts = '';
				foreach($departments as $depid=>$depinfo)
				{
					$opts .= '<option value="'.$depid.'">'.$depinfo['name'].'</option>';
				}
				$RV .= '<tr><th><s5lang>A101900</s5lang>:</th><td><select name="cinfo__lv_department" id="cinfo__lv_department_id">'.$opts.'</select></td></tr>';
			}
			$fields = $this->SInfo['extra_fields'];
			$fields = ($fields=='')?array():unserialize($fields);
			foreach($fields as $a=>$v)
			{
				switch($v['type'])
				{
					case 'text': $RV .= '<tr><th>'.$v['label'].':</th><td><input name="cinfo_'.$v['name'].'" id="cinfo_'.$v['name'].'_id" value="'.$v['default'].'" type="text" /></td></tr>';
					break;
					case 'select': $opt = explode(",",$v['options']);
					$opts = '';
					foreach($opt as $a2=>$v2)
					{
						$v2 = trim($v2);
						if(preg_match('/([^:]+):([^:]+)/',$v2,$m))
						{
							$show = trim($m[2]);
							$value = trim($m[1]);
						}
						else
						{
							$show = $v2;
							$value = $v2;
						}
						if($value == $v['default']) $sel = ' selected ';
						else $sel = '';
						$opts .= '<option'.$sel.'>'.$show.'</option>';
					}
					$RV .= '<tr><th>'.$v['label'].':</th><td><select name="cinfo_'.$v['name'].'" id="cinfo_'.$v['name'].'_id">'.$opts.'</select></td></tr>';
					break;
				}
			}
			return($RV);
		}
		return('');
	}
	function GetCustomThemeJs()
	{
		$RV = '';
		if(isset($this->SInfo['theme_setting']['include_js']))
		{
			$include_js = $this->SInfo['theme_setting']['include_js'];
			if(!is_array($include_js)) $include_js = array($include_js);
			foreach($include_js as $a=>$v)
			{
				$v = InputFilter($v,LIVEADMIN_CON_U.LIVEADMIN_CON_L.LIVEADMIN_CON_D.'-_.');
				if(is_file(LIVEADMIN_T.'/'.$this->SInfo['theme'].'/'.$v))
				{
					$RV .= file_get_contents(LIVEADMIN_T.'/'.$this->SInfo['theme'].'/'.$v)."\n";
				}
			}
		}
		return($RV);
	}
	function ShowPage($page)
	{
		print $page;
	}
	function GetThemeSetting()
	{
		$SETTING = array();
		include(LIVEADMIN_T.'/'.LIVEADMIN_DEFAULT_THEME.'/setting.php');
		if(is_file(LIVEADMIN_T.'/'.$this->SInfo['theme'].'/setting.php'))
		{
			include(LIVEADMIN_T.'/'.$this->SInfo['theme'].'/setting.php');
		}
		$this->SInfo['theme_setting'] = $SETTING;
		return($SETTING);
	}
	function AddClientUniq($client_uniq,$site_key)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$lv_client_cookie = '';
		if(isset($_COOKIE['lv_client']))
			$lv_client_cookie = $_COOKIE['lv_client'];
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$realtime = RealTime();
		$ip = $_SERVER['REMOTE_ADDR'];
		$site_key_esc = mysql_real_escape_string($site_key,$dbh);
		$lv_client_cookie_esc = mysql_real_escape_string($lv_client_cookie,$dbh);
		include_once('geoip.php');
		$geoip = new LV_Geoip();
		$ip_country = strtoupper($geoip->GetInfoString($ip,'country_code'));
		unset($geoip);
		mysql_query("INSERT INTO $tbl SET client_uniq='$client_uniq_esc', client_dtm='$realtime', client_ip='$ip', client_ip_country='$ip_country', site_key='$site_key_esc', client_flag='0', client_cookie='$lv_client_cookie_esc' ",$dbh);
		mysql_close($dbh);
	}
	function CheckBlockAccess($site_key)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'banned';
		$dtm = time();
		if(isset($_COOKIE['lv_client'])) $client_cookie_esc = mysql_real_escape_string($_COOKIE['lv_client'],$dbh);
		else $client_cookie_esc = mysql_real_escape_string(RandomString(24),$dbh);
		$client_ip_esc = mysql_real_escape_string($_SERVER['REMOTE_ADDR'],$dbh);
		$siteid_esc = mysql_real_escape_string(Key2ID($site_key),$dbh);
		$res = mysql_query("SELECT * FROM $tbl WHERE siteid='$siteid_esc' AND active=1 AND ( client_ip='$client_ip_esc' OR client_cookie='$client_cookie_esc' ) AND expiry_date>$dtm ",$dbh);
		$RV = false;
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV = true;
				break;
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function CheckWait($client_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$dtm = RealTime()-(300);
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$res = mysql_query("SELECT * FROM $tbl WHERE client_uniq='$client_uniq_esc' ",$dbh);
		$RV = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV = $req;
				break;
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function GetAgentInfo($userid)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$userid_esc = mysql_real_escape_string($userid,$dbh);
		$res = mysql_query("SELECT * FROM $tbl WHERE userid='$userid_esc' ",$dbh);
		$RV = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV = $req;
				break;
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function GetAgentPic($userid)
	{
		$ainfo = $this->GetAgentInfo($userid);
		if(isset($ainfo['has_pic']) && $ainfo['has_pic']==1) return(true);
		return(false);
	}
	function AgentPic($userid,$size)
	{
		$userid = intval($userid);
		$ainfo = $this->GetAgentInfo($userid);
		$size = InputFilter(strtolower($size),LIVEADMIN_CON_L);
		if($size!='small' && $size!='large') return;
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$userid_esc = mysql_real_escape_string($userid,$dbh);
		$siteid_esc = mysql_real_escape_string($ainfo['siteid'],$dbh);
		$photo_enc = '';
		$tbl = LIVEADMIN_DB_PREFIX.'photo';
		$res = mysql_query("SELECT photo_$size as photo_enc FROM $tbl WHERE userid='$userid_esc' ",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$photo_enc = $req['photo_enc'];
				break;
			}
		}
		mysql_close($dbh);
		if($photo_enc=='') return;
		$photo = decode64($photo_enc);
		header('Content-Type: image/png');
		print $photo;
	}
	function PostNewMessage($message,$client_uniq,$server_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		if($server_uniq===false)
		{
			$tbl = LIVEADMIN_DB_PREFIX.'wait';
			$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
			$res = mysql_query("SELECT server_uniq FROM $tbl WHERE client_uniq='$client_uniq_esc' ",$dbh);
			if($res)
			{
				while($req = mysql_fetch_array($res))
				{
					$server_uniq = $req['server_uniq'];
					break;
				}
			}
		}
		$tbl = LIVEADMIN_DB_PREFIX.'msg';
		if(!lv_is_utf8($message)) $message = lv_convert_utf8($message);
		$message = StripHTML($message);
		if(trim($message)!='')
		{
			$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
			$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
			$realtime = RealTime();
			$msg1 = mysql_real_escape_string(substr($message,0,250),$dbh);
			$msg2 = mysql_real_escape_string(substr($message,250),$dbh);
			mysql_query("INSERT INTO $tbl SET client_uniq='$client_uniq_esc', server_uniq='$server_uniq_esc', dtm='$realtime', direction=2, message_1='$msg1', message_2='$msg2' ",$dbh);
		}
		mysql_close($dbh);
	}
	function PostNewControlMessage($message,$client_uniq,$server_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
		$realtime = RealTime();
		$msg1 = mysql_real_escape_string(substr($message,0,250),$dbh);
		$msg2 = mysql_real_escape_string(substr($message,250),$dbh);
		mysql_query("INSERT INTO $tbl SET client_uniq='$client_uniq_esc', server_uniq='$server_uniq_esc', dtm='$realtime', direction=5, message_1='$msg1', message_2='$msg2' ",$dbh);
		mysql_close($dbh);
	}
	function CloseChat($client_uniq)
	{
		$chat_info = $this->GetChatInfo($client_uniq);
		if(isset($chat_info['client_flag']) && $chat_info['client_flag']<100 && isset($chat_info['client_info_set']) && $chat_info['client_info_set']==1)
		{
			$this->PostCloseMessage('Session closed by client',$_REQUEST['client_uniq'],$chat_info['server_uniq']);
			$this->UpdateClientFlag($_REQUEST['client_uniq'],$_REQUEST['key'],100);
			$this->PostNewControlMessage(CHAT_CLOSED_BY_CLIENT,$_REQUEST['client_uniq'],$chat_info['server_uniq']);
		}
	}
	function GetChatInfo($client_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_uniq = '';
		$client_flag = 0;
		$res = mysql_query("SELECT * FROM $tbl WHERE client_uniq='$client_uniq_esc' ",$dbh);
		$RV = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV = $req;
				break;
			}
		}
		return($RV);
	}
	function PostCloseMessage($message,$client_uniq,$server_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
		$realtime = RealTime();
		$msg1 = mysql_real_escape_string(substr($message,0,250),$dbh);
		$msg2 = mysql_real_escape_string(substr($message,250),$dbh);
		mysql_query("INSERT INTO $tbl SET client_uniq='$client_uniq_esc', server_uniq='$server_uniq_esc', dtm='$realtime', direction=3, message_1='$msg1', message_2='$msg2' ",$dbh);
		mysql_close($dbh);
		return($server_uniq);
	}
	function GetMessageLoop($client_uniq,$server_uniq,$last_check)
	{
		$RV = array();
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
		$last_check_esc = mysql_real_escape_string($last_check,$dbh);
		$realtime = RealTime();
		$auto_link = 0;
		mysql_query("UPDATE $tbl SET client_dtm='$realtime' WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' ",$dbh);
		$res = mysql_query("SELECT client_flag,server_auto_link FROM $tbl WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' ",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV['ctl_client_flag'] = $req['client_flag'];
				$auto_link = $req['server_auto_link'];
				break;
			}
		}
		$tbl = LIVEADMIN_DB_PREFIX.'msg';
		$res = mysql_query("SELECT * FROM $tbl WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' AND dtm>$last_check_esc AND dtm<=$realtime ORDER BY dtm ",$dbh);
		$RV['ctl_last_check'] = 'LC'.$realtime;
		$RV['ctl_status'] = 1;
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				switch($req['direction'])
				{
					case 1: $m = $req['message_1'].$req['message_2'];
					if($auto_link==1) $m = AutoLink($m);
					if(isset($req['msg_userid']) && $req['msg_userid']>0)
					{
						$ext_uinfo = GetUserInfo('userid',$req['msg_userid']);
						if($ext_uinfo!==false)
						{
							$m = '~u~'.$ext_uinfo['nickname'].'~'.$m;
						}
					}
					$RV['msg_1_'.$req['dtm']] = liveadmin_encode64($m);
					break;
					case 2: $m = $req['message_1'].$req['message_2'];
					if($auto_link==1) $m = AutoLink($m);
					$RV['msg_2_'.$req['dtm']] = liveadmin_encode64($m);
					break;
					case 4: $RV['msg_4_'.$req['dtm']] = liveadmin_encode64($req['message_1'].$req['message_2']);
					break;
					case 6: $RV['msg_6_'.$req['dtm']] = liveadmin_encode64($req['message_1'].$req['message_2']);
					break;
				}
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function ModLite()
	{
		if(LIVEADMIN_LITE)
		{
			$this->SInfo['enable_callback'] = 0;
			$this->SInfo['auto_invite'] = 0;
			$this->SInfo['show_affiliate_link'] = 1;
		}
	}
	function PostUserInfo($client_uniq,$site_key,$r)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$cinfo = array();
		foreach($r as $a=>$v)
		{
			if(substr($a,0,6)=='cinfo_')
			{
				$cinfo[substr($a,6)] = StripHTML($v);
			}
		}
		if(ArrayMember($_REQUEST,'dc','')=='y')
		{
			$tbl = LIVEADMIN_DB_PREFIX.'visitors';
			$siteid_esc = mysql_real_escape_string(Key2ID($site_key),$dbh);
			$ip_esc = mysql_real_escape_string($_SERVER['REMOTE_ADDR'],$dbh);
			$res = mysql_query("SELECT caller_sid FROM $tbl WHERE siteid=$siteid_esc AND ip='$ip_esc' ",$dbh);
			if($res)
			{
				while($req = mysql_fetch_assoc($res))
				{
					$parts = explode(':',$req['caller_sid']);
					$cinfo['_lv_caller_sid'] = liveadmin_encode64($parts[0]);
					if(isset($parts[1]) && $parts[1]!=-1)
					{
						$cinfo['_lv_department'] = liveadmin_encode64($parts[1]);
					}
					break;
				}
			}
		}
		if(isset($r['laef']))
		{
			$laef = @unserialize(decode64($r['laef']));
			if(is_array($laef))
			{
				foreach($laef as $a=>$v)
				{
					$key = liveadmin_decode64($a);
					$fkey = $key;
					for($i=1;$i<1000;$i++)
					{
						if(!isset($cinfo[$fkey])) break;
						$fkey = $key.'_'.$i;
					}
					$cinfo[$fkey] = $v;
				}
			}
		}
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_info = serialize($cinfo);
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$realtime = RealTime();
		$site_key_esc = mysql_real_escape_string($site_key,$dbh);
		$client_info_esc = mysql_real_escape_string($client_info,$dbh);
		$client_nickname_sec = mysql_real_escape_string(StripHTML($r['client_nickname']),$dbh);
		$tflag = 1;
		if(ArrayMember($_REQUEST,'dc','')=='y')
		{
			$tflag = 7;
		}
		mysql_query("UPDATE $tbl SET client_info='$client_info_esc', client_info_set=1, client_dtm='$realtime', client_nickname='$client_nickname_sec', client_flag=$tflag WHERE client_uniq='$client_uniq_esc' AND site_key='$site_key_esc' ",$dbh);
		mysql_close($dbh);
		print AjaxPack(array('post_user_info_status'=>1));
	}
	function UpdateClientFlag($client_uniq,$site_key,$flag)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$site_key_esc = mysql_real_escape_string($site_key,$dbh);
		$flag_esc = mysql_real_escape_string($flag,$dbh);
		mysql_query("UPDATE $tbl SET client_flag='$flag_esc' WHERE client_uniq='$client_uniq_esc' AND site_key='$site_key_esc' ",$dbh);
		mysql_close($dbh);
	}
	function PostTakeMessage($client_uniq,$site_key,$r)
	{
		if($this->SInfo['demo_mode']==1)
		{
			print AjaxPack(array('post_take_message_status'=>1));
			return false;
		}
		$name = $r['tm_name'];
		$email = $r['tm_email'];
		$text = $r['tm_text'];
		$email = str_replace(array("\n","\r"),'',trim($email));
		if($email!='') $from_email = $email;
		else $from_email = $this->SInfo['no_answer_email'];
		$headers = 'From: '.$from_email. "\r\n" . 'Reply-To: '.$from_email. "\r\n" . 'X-Mailer: LiveAdmin/' . $this->SInfo['company'];
		$msg = '';
		$msg .= 'Message from LiveAdmin on '.$this->SInfo['company']."\r\n";
		$msg .= '---------------------------------------------------------------'."\r\n";
		$msg .= 'Name: '.$name."\r\n";
		$msg .= 'Email: '.$email."\r\n";
		$msg .= 'Message: '."\r\n";
		$msg .= $text."\r\n";
		$msg .= '---------------------------------------------------------------'."\r\n";
		$invar = array ( 'from_name'=>$name, 'from_email'=>$email, 'to_name'=>$this->SInfo['company'], 'to_email'=>$this->SInfo['no_answer_email'], 'subject'=>'Message from LiveAdmin on '.$this->SInfo['company'], 'company'=>$this->SInfo['company'], 'body'=>$text, 'type'=>$this->SInfo['no_answer_email_type'], 'siteid'=>$this->SInfo['siteid'], 'language'=>$this->SInfo['language'] );
		if(LIVEADMIN_STANDALONE)
		{
			include_once('vmail.php');
			$invar['stnl_sinfo'] = $this->SInfo;
			$admin_in = false;
			$vmail = new LV_Mail($admin_in);
			$vmail->SendClientEmail($invar);
			unset($vmail);
		}
		print AjaxPack(array('post_take_message_status'=>1));
		return true;
	}
	function DefaultProcessor()
	{
		$vsid = $this->NewVisitor();
		$online_image = '';
		if($this->SInfo['online_image']!='')
			$online_image = $this->SInfo['online_image'];
		elseif(is_file(LIVEADMIN_FOF.'/'.$this->SInfo['on_off_theme'].'_on.png'))
			$online_image = LIVEADMIN_WOF.'/'.$this->SInfo['on_off_theme'].'_on.png';
		else
			$online_image = LIVEADMIN_WOF.'/tm_02_on.png';
		$offline_image = '';
		if($this->SInfo['offline_image']!='')
			$offline_image = $this->SInfo['offline_image'];
		elseif(is_file(LIVEADMIN_FOF.'/'.$this->SInfo['on_off_theme'].'_off.png'))
			$offline_image = LIVEADMIN_WOF.'/'.$this->SInfo['on_off_theme'].'_off.png';
		else
			$offline_image = LIVEADMIN_WOF.'/tm_02_off.png';
		$this->SInfo['invalid_image'] = LIVEADMIN_WCC.'/live_chat_invalid.png';
		$opts = ChatWindowOptions(unserialize($this->SInfo['chat_window_options']));
		$css = $this->TranslateTheme($this->LoadThemeFileMulti('liveadmin.css'))."\n";
		if(ArrayMember($opts,'invite_image_url','')!='' && ArrayMember($opts,'invite_image_width',0)!=0 && ArrayMember($opts,'invite_image_height',0)!=0)
		{
			$css .= "div.lv_invite_img{background-image: url('".$opts['invite_image_url']."');width: ".$opts['invite_image_width']."px;height:".$opts['invite_image_height']."px;}\n";
		}
		if(ArrayMember($opts,'invite_image_yes_x','')!='' && ArrayMember($opts,'invite_image_yes_y','')!='' && ArrayMember($opts,'invite_image_yes_w','')!='' && ArrayMember($opts,'invite_image_yes_h','')!='')
		{
			$css .= "div.lv_invite div.lv_yes {	left: ".$opts['invite_image_yes_x']."px;top: ".$opts['invite_image_yes_y']."px;width: ".$opts['invite_image_yes_w']."px;height: ".$opts['invite_image_yes_h']."px;}\n";
		}
		if(ArrayMember($opts,'invite_image_no_x','')!='' && ArrayMember($opts,'invite_image_no_y','')!='' && ArrayMember($opts,'invite_image_no_w','')!='' && ArrayMember($opts,'invite_image_no_h','')!='')
		{
			$css .= "div.lv_invite div.lv_no {	left: ".$opts['invite_image_no_x']."px;top: ".$opts['invite_image_no_y']."px;width: ".$opts['invite_image_no_w']."px;height: ".$opts['invite_image_no_h']."px;}\n";
		}
		if(ArrayMember($opts,'auto_invite_mode','')=='')
		{
			$opts['auto_invite_mode'] = 0;
		}
		$tag = "liveadmin";
		if(isset($_REQUEST['tag']))
			$tag = $_REQUEST['tag'];
		if(isset($_REQUEST['key']))
			$script_uniq = md5($tag.':'.$_REQUEST['key']);
		else
			$script_uniq = md5($tag);
		$js ='';
		$js .= 'if(typeof(Live_'.$script_uniq.'_Admin)=="undefined"){Live_'.$script_uniq.'_Admin = {};}';
		$js .= 'var Live_'.$script_uniq.'_AdminConf = {';
		$js .= 'conf_status_image_id : "'.$tag.'",';
		$js .= 'conf_tag : "'.$tag.'",';
		$js .= 'conf_init_interval: 50,';
		$js .= 'conf_init_timeout: 800,';
		$js .= 'conf_callback_interval_s: 5,';
		$js .= 'conf_callback_check_s: 1,';
		$js .= 'conf_online_status: '.$this->SInfo['online_status'].', ';
		$js .= 'conf_online_image: "'.$online_image.'",';
		$js .= 'conf_vsid: "'.$vsid.'",';
		$js .= 'conf_offline_image: "'.$offline_image.'",';
		$js .= 'conf_invalid_image: "'.$this->SInfo['invalid_image'].'",';
		if(!isset($this->SInfo['theme_setting']))
			$this->GetThemeSetting();
		foreach($this->SInfo['theme_setting'] as $a=>$v)
		{
			if(substr($a,0,3)=='js_')
			{
				$a = substr($a,3);
				if(is_numeric($v))
					$js .= $a.': '.$v.',';
				else
					$js .= $a.': "'.$v.'",';
			}
		}
		$js .= 'conf_title: "'.$this->SInfo['company'].'",';
		$js .= 'conf_offline_act:'.intval($this->SInfo['offline_act']).',';
		$js .= 'conf_auto_invite:'.intval($this->SInfo['auto_invite']).',';
		$js .= 'conf_auto_invite_delay:'.$this->SInfo['auto_invite_delay'].',';
		$js .= 'conf_iframe: "'.LIVEADMIN_URL_CHAT.'?key='.$this->SInfo['key'].(isset($_REQUEST['client_nickname'])&&$_REQUEST['client_nickname']?'&client_nickname='.$_REQUEST['client_nickname']:'').'",';
		if(!LIVEADMIN_STANDALONE)
		{
			$js .= 'conf_lv_standalone: "n",';
			switch($this->SInfo['enable_callback'])
			{
				case 0:
					$js .= 'conf_enable_callback: "n",';
				break;
				case 1:
					$js .= 'conf_enable_callback: "y",';
				break;
				case 2:
					$js .= 'conf_enable_callback: "yi",';
				break;
			}
			$js .= 'conf_callback: "'.LIVEADMIN_URL_CALLBACK.'vs_'.$vsid.'.gif?lv=1",';
		}
		else
		{
			$js .= 'conf_lv_standalone: "y",';
			switch($this->SInfo['enable_callback'])
			{
				case 0:
					$js .= 'conf_enable_callback: "n",';
				break;
				case 1:
					$js .= 'conf_enable_callback: "y",';
				break;
				case 2:
					$js .= 'conf_enable_callback: "yi",';
				break;
			}
			$js .= 'conf_callback: "'.LIVEADMIN_URL_CALLBACK.'?key='.$this->SInfo['key'].'&vsid='.$vsid.'",';
		}
		$js .= 'conf_style_enc: "'.liveadmin_encode64($css).'",';
		foreach($opts as $a=>$v)
		{
			if($a=='google_map_api_key')
				continue;
			if($a=='departments')
				continue;
			if(substr($a,0,13)=='invite_image_')
				continue;
			$js .= 'opt_'.$a.': "'.$v.'",';
		}
		$js .= 'server_uniq: ""';
		$js .= '};'."\n\n";
		if(LIVEADMIN_STANDALONE)
		{
			$myPacker = new JavaScriptPacker($js, 'Normal', true, false);
			$js = $myPacker->pack();
			$lv_script = file_get_contents(LIVEADMIN_AST.'/client_btn-decoded.js')."\n\n";
			$lv_script = str_replace('LiveAdmin','Live_'.$script_uniq.'_Admin',$lv_script);
			$lv_script .= 'Live_'.$script_uniq.'_Admin.Init();'."\n\n";
			$myPacker = new JavaScriptPacker($lv_script, 'Normal', true, false);
			$js .= "\n".$myPacker->pack();
		}
		header("Content-Type: application/x-javascript");
		header("Content-Length: ".strlen($js));
		print $js;
	}
	function NewVisitor()
	{
		$_SERVER['REMOTE_ADDR'] = trim($_SERVER['REMOTE_ADDR']);
		if($_SERVER['REMOTE_ADDR']=='')
			return;
		include_once('browser.php');
		$brw = new LV_Browser();
		$browser = $brw->Name.' '.$brw->Version;
		unset($brw);
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'visitors';
		$siteid_esc = mysql_real_escape_string($this->SInfo['siteid'],$dbh);
		$url_esc = mysql_real_escape_string(ArrayMember($_SERVER,'HTTP_REFERER'),$dbh);
		$tag_esc = mysql_real_escape_string(ArrayMember($_REQUEST,'page_tag'),$dbh);
		$browser_esc = mysql_real_escape_string($browser,$dbh);
		$ip_esc = mysql_real_escape_string($_SERVER['REMOTE_ADDR'],$dbh);
		$dtm = RealTime();
		mysql_query("INSERT INTO $tbl SET siteid='$siteid_esc', ip='$ip_esc', dtm=$dtm, url='$url_esc', tag='$tag_esc', browser='$browser_esc' ON DUPLICATE KEY UPDATE dtm=$dtm, url='$url_esc', tag='$tag_esc', browser='$browser_esc' ",$dbh);
		mysql_close($dbh);
		return($_SERVER['REMOTE_ADDR']);
	}
	function SetClientCookie()
	{
		if(isset($_COOKIE['lv_client']) && strlen($_COOKIE['lv_client'])==32)
			return;
		setcookie("lv_client",RandomString(32),time()+(30*24*3600),"/"/*,".liveadmin.net"*/);
	}
	function BaseProcessor()
	{
		$js = file_get_contents(LIVEADMIN_F.'/client/liveadmin.js')."\n\n";
		$myPacker = new JavaScriptPacker($js, 'Numeric', true, false);
		$js = $myPacker->pack();
		header("Content-Type: application/x-javascript");
		header("Content-Length: ".strlen($js));
		print $js;
	}
	function SetDefaultTexts($texts)
	{
		if(!is_array($texts)) $texts = array();
		foreach($_SERVER['default_texts'] as $a=>$v)
		{
			if(!isset($texts[$a])) $texts[$a] = $v;
		}
		return($texts);
	}
}
?>