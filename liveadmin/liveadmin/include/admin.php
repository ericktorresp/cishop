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
class LV_Admin
{
	private $SInfo;
	private $ValidSite;
	function LV_Admin($site_id_in = false)
	{
		if($site_id_in!==false)
		{
			$this->SInfo = GetSiteInfo('siteid',$site_id_in);
			$this->ValidSite = IsValid_SInfo($this->SInfo);
			if($this->ValidSite!==true)
			{
				$this->SInfo = array();
			}
			else
			{
				$this->SInfo['key'] = ID2Key($this->SInfo['siteid']);
			}
		}
	}
	function Run()
	{
		if(isset($_REQUEST['null']) && $_REQUEST['null']=='yes' && !isset($_REQUEST['act']))
		{
			print "1";
			return;
		}
		ob_start();
		$_SERVER['LIVEADMIN_MENU'] = '';
		$_SERVER['LIVEADMIN_TITLE'] = '';
		$_SERVER['LIVEADMIN_CONF'] = array();
		$this->GetPage();
		$LIVEADMIN_CONTENTS = ob_get_contents();
		ob_end_clean();
		if(!isset($_SERVER['uinfo']['language'])) $_SERVER['uinfo']['language'] = 'english';
		$lang = LoadLang($_SERVER['uinfo']['language']);
		$LIVEADMIN_CONTENTS = $lang->Translate($LIVEADMIN_CONTENTS);
		print $LIVEADMIN_CONTENTS;
	}
	function GetPage()
	{
		$blank_act = false;
		if(!isset($_REQUEST['act']) || $_REQUEST['act']=='')
		{
			$blank_act = true;
			$_REQUEST['act'] = 'home';
		}
		$act = $_REQUEST['act'];
		$_SERVER['act'] = $act;
		$_SERVER['LIVEADMIN_TITLE'] = 'Live Admin';
		if($act=='do_login' || $act=='login')
		{
			$_SERVER['LIVEADMIN_NO_THEME'] = true;
			include('login.php');
		}
		elseif(LIVEADMIN_STANDALONE && $act=='install')
		{
			$_SERVER['LIVEADMIN_NO_THEME'] = true;
			include('install.php');
			$lv_install = new LV_LiveAdminInstall();
			$lv_install->Run();
			unset($lv_install);
		}
		elseif(LIVEADMIN_STANDALONE && $blank_act && !$this->IsInstalled())
		{
			header('Location: ?act=install');
			exit;
		}
		else
		{
			if(CheckLogin()!==true) exit;
			switch($act)
			{
				default: case 'home': $this->AutoClose();
				$this->ResetInternCheck(false);
				if(LIVEADMIN_STANDALONE) $this->SyncDatabases();
				$_SERVER['LIVEADMIN_MENU'] = true;
				$_SERVER['LIVEADMIN_TITLE'] = 'Live Admin';
				$_SERVER['LIVEADMIN_CHAT_PANEL']['p1'] = liveadmin_encode64($this->GetContentsPart(LIVEADMIN_FC.'/chat_panel_p1.php','chat_panel_p1'));
				$_SERVER['LIVEADMIN_CHAT_PANEL']['p1_config_1'] = liveadmin_encode64($this->GetContentsPart(LIVEADMIN_FC.'/chat_panel_p1.php','chat_panel_config_'.$_SERVER['uinfo']['access_level']));
				$_SERVER['LIVEADMIN_CONF'] = $this->LiveAdminConf();
				include('temp.php');
				break;
				case 'redhome': include('redhome.php');
				break;
				case 'logout': $this->Logout();
				DoLogout();
				break;
				case 'news': $this->CleanupHistory();
				$this->CleanupWait();
				PrintJsonPack($this->GetNews());
				break;
				case 'waiting_clients': if(LIVEADMIN_STANDALONE)
				{
					$this->CleanupVisitors();
				}
				$this->OutWaitingClients();
				break;
				case 'start_chat': $this->StartChat($_REQUEST['client']);
				$this->AutoClose();
				break;
				case 'start_visitor_chat': $this->StartVisitorChat($_REQUEST['ip']);
				break;
				case 'null': PrintJsonPack(array('status'=>1));
				break;
				case 'message': $this->PostNewMessage($_REQUEST['message'],$_REQUEST['client_uniq'],$_REQUEST['server_uniq']);
				$this->PostNewHistory($_REQUEST['message']);
				break;
				case 'message_loop': $mloop = $this->GetMessageLoop($_REQUEST['client_uniq'],$_REQUEST['server_uniq'],substr($_REQUEST['last_check'],2)*1);
				PrintJsonPack($mloop);
				break;
				case 'discard_chat': $this->DiscardChat($_REQUEST['client_uniq']);
				break;
				case 'onhold_chat': $this->SetChatFlag($_REQUEST['client_uniq'],$_REQUEST['server_uniq'],6);
				$this->PostNewMessage('<s5lang>Session on hold by</s5lang> '.$_SERVER['uinfo']['nickname'],$_REQUEST['client_uniq'],$_REQUEST['server_uniq'],3);
				break;
				case 'resume_chat': $this->SetChatFlag($_REQUEST['client_uniq'],$_REQUEST['server_uniq'],4);
				$this->PostNewMessage('<s5lang>Session resumed by</s5lang> '.$_SERVER['uinfo']['nickname'],$_REQUEST['client_uniq'],$_REQUEST['server_uniq'],3);
				break;
				case 'close_chat': $this->CloseChat($_REQUEST['client_uniq'],$_REQUEST['server_uniq'],$_SERVER['uinfo']['nickname']);
				break;
				case 'block_access': $res = $this->BlockAccess($_REQUEST['client_uniq'],$_REQUEST['server_uniq'],$_REQUEST['period_hours']);
				$this->CloseChat($_REQUEST['client_uniq'],$_REQUEST['server_uniq'],$_SERVER['uinfo']['nickname']);
				PrintJsonPack($res);
				break;
				case 'archive_missed_call': $this->ArchiveMissedCall($_REQUEST['client_uniq']);
				break;
				case 'delete_missed_call': $this->DeleteMissedCall($_REQUEST['client_uniq']);
				break;
				case 'control': $this->PostNewMessage($_REQUEST['message'],$_REQUEST['client_uniq'],$_REQUEST['server_uniq'],6);
				break;
				case 'liveadminconf': PrintJsonPack($this->LiveAdminConf());
				break;
				case 'message_intern': PrintJsonPack($this->PostNewInternMessage($_REQUEST['message'],$_REQUEST['userid']));
				break;
				case 'get_agents': PrintJsonPack($this->GetAgents(),false);
				break;
				case 'get_fields': PrintJsonPack($this->GetFields(),false);
				break;
				case 'get_site_news': PrintJsonPack($this->GetSiteNews(),false);
				break;
				case 'get_blocked_clients': PrintJsonPack($this->GetBlockedClients(),false);
				break;
				case 'get_departments': PrintJsonPack($this->GetDepartments(),false);
				break;
				case 'blocked_clients_modify': PrintJsonPack($this->ModifyBlockedClients());
				break;
				case 'get_dialog_body': include_once('dialog.php');
				$lv_dialog = new LV_Dialog($this);
				$dialog = $_REQUEST['dialog'];
				if($dialog=='dialog_affiliate')
				{
					$this->CalculateAffiliateBalance();
				}
				switch($dialog)
				{
					case 'dialog_strings': PrintJsonPack($lv_dialog->GetDialogStrings());
					break;
					case 'dialog_users': PrintJsonPack($lv_dialog->GetDialogUsers());
					break;
					case 'dialog_installation': PrintJsonPack($lv_dialog->GetDialogInstallation());
					break;
					default: $dstruct = $lv_dialog->GetDialogsStruct();
					if(isset($dstruct[$dialog]))
					{
						$dhtml = $lv_dialog->GetDialogsHtml(array($dialog=>$dstruct[$dialog]));
						PrintJSonPack($dhtml[$dialog]);
					}
					break;
				}
				break;
				case 'get_sounds': PrintJsonPack($this->GetSounds());
				break;
				case 'get_all_sounds': PrintJsonPack($this->GetAllSounds());
				break;
				case 'get_lic_info': PrintJsonPack($this->GetLicInfo());
				break;
				case 'set_lic_info': PrintJsonPack($this->SetLicInfo());
				break;
				case 'get_all_on_off_themes': include_once('dialog.php');
				$lv_dialog = new LV_Dialog($this);
				PrintJsonPack(array('status'=>1,'list'=>$lv_dialog->GetStatusThemes()));
				break;
				case 'get_all_themes': include_once('dialog.php');
				$lv_dialog = new LV_Dialog($this);
				PrintJsonPack(array('status'=>1,'list'=>$lv_dialog->GetThemes()));
				break;
				case 'update_admin_panel_pref': PrintJsonPack($this->UpdateUsersPref($_REQUEST,'dialog_adminside'));
				break;
				case 'update_site_panel_pref': PrintJsonPack($this->UpdateSitesPref($_REQUEST,'dialog_clientside'));
				break;
				case 'update_profile_pref': PrintJsonPack($this->UpdateUsersPref($_REQUEST,'dialog_profile'));
				break;
				case 'update_chat_panel_pref': PrintJsonPack($this->UpdateSitesPref($_REQUEST,'dialog_chatpanel'));
				break;
				case 'update_password_pref': PrintJsonPack($this->UpdatePasswordPref($_REQUEST));
				break;
				case 'update_strings_pref': PrintJsonPack($this->UpdateStringsPref($_REQUEST));
				break;
				case 'update_agents_add_pref': PrintJsonPack($this->UpdateAgentsAddPref($_REQUEST));
				break;
				case 'update_agents_edit_pref': PrintJsonPack($this->UpdateAgentsEditPref($_REQUEST));
				break;
				case 'update_agents_edit_single_pref': PrintJsonPack($this->UpdateAgentsEditSinglePref($_REQUEST));
				break;
				case 'update_agents_delete_pref': PrintJsonPack($this->UpdateAgentsDeletePref($_REQUEST));
				break;
				case 'update_fields_add_pref': PrintJsonPack($this->UpdateFieldsAddPref($_REQUEST));
				break;
				case 'update_fields_edit_pref': PrintJsonPack($this->UpdateFieldsEditPref($_REQUEST));
				break;
				case 'update_fields_delete_pref': PrintJsonPack($this->UpdateFieldsDeletePref($_REQUEST));
				break;
				case 'update_invite_dialog': PrintJsonPack($this->UpdateSitesPref($_REQUEST,'dialog_invite_image'));
				break;
				case 'update_departments_add_pref': PrintJsonPack($this->UpdateDepartmentAddPref($_REQUEST));
				break;
				case 'update_departments_edit_pref': PrintJsonPack($this->UpdateDepartmentEditPref($_REQUEST));
				break;
				case 'update_departments_delete_pref': PrintJsonPack($this->UpdateDepartmentDeletePref($_REQUEST));
				break;
				case 'update_news_add_pref': PrintJsonPack($this->UpdateNewsAddPref($_REQUEST));
				break;
				case 'update_news_edit_pref': PrintJsonPack($this->UpdateNewsEditPref($_REQUEST));
				break;
				case 'update_news_delete_pref': PrintJsonPack($this->UpdateNewsDeletePref($_REQUEST));
				break;
				case 'update_photo_pref': PrintJsonPackEnc($this->UpdatePhotoPref($_REQUEST,$_FILES));
				break;
				case 'update_photo_delete_pref': PrintJsonPack($this->UpdatePhotoDeletePref($_REQUEST));
				break;
				case 'get_agent_photo': $this->GetAgentPhoto($_REQUEST['size']);
				break;
				case 'auto_complete': $ac = $this->GetAutoComplete();
				print implode("\n",$ac);
				break;
				case 'delete_history': $this->DeleteHistory($_REQUEST['query_md5']);
				break;
				case 'get_log_list': PrintJsonPack($this->GetLogList($_REQUEST),false);
				break;
				case 'get_log_msg': PrintJsonPack($this->GetLogMsg($_REQUEST));
				break;
				case 'delete_log': $this->DeleteLog($_REQUEST);
				break;
				case 'send_log_by_email': include_once('vmail.php');
				$vmail = new LV_Mail($this);
				PrintJsonPack($vmail->SendLogEmail($_REQUEST));
				break;
				case 'add_draft': $this->AddDraft($_REQUEST['message']);
				break;
				case 'delete_draft': PrintJsonPack($this->DeleteDraft($_REQUEST['msg_md5']));
				break;
				case 'get_drafts': PrintJsonPack($this->GetDrafts(),false);
				break;
				case 'make_offline': $this->Logout();
				break;
				case 'reports_agents_average_answering_time': include_once('reports.php');
				$repo = new LV_Reports($this);
				PrintJsonPack($repo->GetReport($act),false);
				unset($repo);
				break;
				case 'reports_requests_count': include_once('reports.php');
				$repo = new LV_Reports($this);
				PrintJsonPack($repo->GetReport($act),false);
				unset($repo);
				break;
				case 'register': PrintJsonPack($this->DoReg());
				break;
				case 'check_upgrade': PrintJsonPack($this->CheckUpgrade());
				break;
				case 'do_upgrade': PrintJsonPack($this->DoUpgrade());
				break;
				case 'start_upgrade': $this->StartUpgrade();
				break;
				case 'loop_upgrade': PrintJsonPack($this->LoopUpgrade());
				break;
				case 'upgrade_modify': PrintJsonPack($this->ModifyUpgradeFlag());
				break;
				case 'get_transfer_list_tree': PrintJsonPack($this->GetTransferListTree());
				break;
				case 'chat_transfer': PrintJsonPack($this->ChatTransfer());
				break;
			}
		}
	}
	function Logout()
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$site_id_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$user_id_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$last_act = time()-7200;
		$tbl = LIVEADMIN_DB_PREFIX.'sites';
		mysql_query("UPDATE $tbl SET last_act='$last_act' WHERE siteid='$site_id_esc' ",$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		mysql_query("UPDATE $tbl SET last_uact='$last_act' WHERE userid='$user_id_esc' AND siteid='$site_id_esc' ",$dbh);
		mysql_close($dbh);
	}
	function IsInstalled()
	{
		if(strpos(LIVEADMIN_B,'<%value')!==false) return false;
		return true;
	}
	function CanCreateUser($user)
	{
		$reserve_users = array ( );
		foreach($reserve_users as $u)
		{
			if(preg_match("/$u/",$user)) return false;
		}
		return true;
	}
	function GetWaitingClients()
	{
		$this->CheckWaitList();
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$dtm = RealTime()-(300);
		$dtm_missed_call = RealTime()-($_SERVER['uinfo']['show_missed_calls']*24*3600);
		$site_key_esc = mysql_real_escape_string($_SERVER['sinfo']['key'],$dbh);
		$site_id_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$user_id_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$res = mysql_query("SELECT * FROM $tbl WHERE site_key='$site_key_esc' AND client_info_set=1 ",$dbh);
		$RV = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV[] = $req;
			}
		}
		$last_act = time();
		if(isset($_REQUEST['online_status']) && $_REQUEST['online_status']=='yes')
		{
			$tbl = LIVEADMIN_DB_PREFIX.'sites';
			mysql_query("UPDATE $tbl SET last_act='$last_act' WHERE siteid='$site_id_esc' ",$dbh);
			$tbl = LIVEADMIN_DB_PREFIX.'users';
			mysql_query("UPDATE $tbl SET last_uact='$last_act' WHERE userid='$user_id_esc' AND siteid='$site_id_esc' ",$dbh);
		}
		mysql_close($dbh);
		$RV2 = array();
		foreach($RV as $a=>$v)
		{
			if(($v['client_flag']==100 || $v['client_flag']==101) && $v['client_dtm']<RealTime()-60)
			{
				$this->ArchiveChat($v['client_uniq'],$v['server_uniq']);
			}
			if($v['client_dtm']>$dtm || ($v['client_dtm']>$dtm_missed_call && $v['client_flag']==300 ) ) $RV2[] = $v;
		}
		if($_SERVER['sinfo']['expiry_date']<time())
		{
			return(array());
		}
		return($RV2);
	}
	function GetOnlineAgents($all_agents = false)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$dtm = time()-(300);
		$site_id_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$user_id_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		if($all_agents) $res = mysql_query("SELECT * FROM $tbl WHERE siteid='$site_id_esc' ORDER BY nickname ",$dbh);
		else $res = mysql_query("SELECT * FROM $tbl WHERE siteid='$site_id_esc' AND last_uact>$dtm  ORDER BY nickname ",$dbh);
		$RV = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				if($req['last_uact']>$dtm) $req['agent_online'] = true;
				else $req['agent_online'] = false;
				$RV[] = $req;
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function SortOnlineAgents($oa)
	{
		$RV_SELF = false;
		$RV_ON = array();
		$RV_OFF = array();
		$RV_SUS = array();
		foreach($oa as $a=>$v)
		{
			if($_SERVER['uinfo']['userid']==$v['userid'] && $v['agent_online'])
			{
				$RV_SELF = $v;
			}
			elseif($v['ac_status']==0)
			{
				$RV_SUS[] = $v;
			}
			elseif($v['agent_online'])
			{
				$RV_ON[] = $v;
			}
			else
			{
				$RV_OFF[] = $v;
			}
		}
		$RV = array();
		if(is_array($RV_SELF)) $RV[] = $RV_SELF;
		$RV = array_merge($RV,$RV_ON);
		$RV = array_merge($RV,$RV_OFF);
		$RV = array_merge($RV,$RV_SUS);
		return($RV);
	}
	function CheckWaitList()
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$dtm = RealTime()-(3600);
		mysql_query("UPDATE $tbl SET client_flag=100 WHERE client_dtm<$dtm AND client_flag<100 ",$dbh);
		mysql_query("UPDATE $tbl SET client_flag=300 WHERE client_flag=100 AND server_uniq is NULL ",$dbh);
		mysql_close($dbh);
	}
	function CleanupWait()
	{
		$this->GetWaitingClients();
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$dtm = RealTime()-(3*3600);
		$res = mysql_query("SELECT * FROM $tbl WHERE client_info_set=1 AND (client_flag=100 OR client_flag=101) AND client_dtm<$dtm ",$dbh);
		$RV = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV[] = $req;
			}
		}
		mysql_close($dbh);
		foreach($RV as $a=>$v)
		{
			$this->ArchiveChat($v['client_uniq'],$v['server_uniq']);
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$dtm = RealTime()-(7*24*3600);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		mysql_query("DELETE FROM $tbl WHERE client_dtm<$dtm ",$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg';
		mysql_query("DELETE FROM $tbl WHERE dtm<$dtm ",$dbh);
		mysql_close($dbh);
	}
	function AutoClose()
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$dtm = RealTime()-(3600);
		$site_key_esc = mysql_real_escape_string($_SERVER['sinfo']['key'],$dbh);
		$site_id_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		mysql_query("DELETE FROM $tbl WHERE site_key='$site_key_esc' AND client_dtm<$dtm AND client_info_set=0 ",$dbh);
		$res = mysql_query("SELECT server_uniq,client_uniq,server_nickname FROM $tbl WHERE site_key='$site_key_esc' AND client_dtm<$dtm AND client_info_set=1 ",$dbh);
		$RV = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV[] = $req;
			}
		}
		mysql_close($dbh);
		foreach($RV as $a=>$v)
		{
			$this->CloseChat($v['client_uniq'],$v['server_uniq'],$v['server_nickname']);
		}
	}
	function CloseChat($client_uniq,$server_uniq,$server_nickname)
	{
		if($this->GetChatFlag($client_uniq,$server_uniq)<100)
		{
			$this->SetChatFlag($client_uniq,$server_uniq,101);
			$this->PostNewMessage('<s5lang>Session Closed by</s5lang> '.$server_nickname,$client_uniq,$server_uniq,3);
			$this->PostNewMessage(CHAT_CLOSED_BY_ADMIN,$client_uniq,$server_uniq,5);
		}
	}
	function BlockAccess($client_uniq,$server_uniq,$period_hours)
	{
		$RV = array();
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$res = mysql_query("SELECT client_ip, client_cookie FROM $tbl WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' ",$dbh);
		$client_ip = '';
		$client_cookie = '';
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$client_ip = $req['client_ip'];
				$client_cookie = $req['client_cookie'];
				break;
			}
		}
		if($client_ip == '' && $client_cookie == '')
		{
			mysql_close($dbh);
			$RV['status'] = 1;
			return($RV);
		}
		$tbl = LIVEADMIN_DB_PREFIX.'banned';
		$bid = false;
		$res = mysql_query("SELECT * FROM $tbl WHERE siteid='$siteid_esc' AND ( client_ip='$client_ip' AND client_cookie='$client_cookie' ) ",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$bid = $req['bid'];
				break;
			}
		}
		$dtm = time()+($period_hours*3600);
		if($bid===false)
		{
			mysql_query("INSERT INTO $tbl SET siteid='$siteid_esc', active=1, client_ip = '$client_ip', client_cookie='$client_cookie', expiry_date='$dtm', userid='$userid_esc' ",$dbh);
		}
		else
		{
			mysql_query("UPDATE $tbl SET active=1, expiry_date='$dtm', userid='$userid_esc' WHERE siteid='$siteid_esc' AND bid='$bid' ",$dbh);
		}
		mysql_close($dbh);
		$RV['status'] = 1;
		return($RV);
	}
	function CountryToFlagPos($code,&$flag_x,&$flag_y)
	{
		if($code=='' || $code=='--')
		{
			$flag_x = 0;
			$flag_y = 0;
		}
		else
		{
			$code = strtoupper($code);
			$flag_x = (ord(substr($code,0,1))-ord('A'))*22;
			$flag_y = (ord(substr($code,1,1))-ord('A'))*15;
		}
	}
	function ClientInfo2Array($client_info, $hide_scores = false,$pack_again = false)
	{
		$changed = false;
		if(!is_array($client_info))
		{
			$client_info_dec = liveadmin_decode64(unserialize($client_info));
		}
		else
		{
			$client_info_dec = $client_info;
		}
		if(isset($client_info_dec['_lv_department']))
		{
			$depid = $client_info_dec['_lv_department'];
			$departments = lv_get_departments($_SERVER['sinfo']);
			if(isset($departments[$depid]))
			{
				$client_info_dec['lv_department_res'] = $departments[$depid]['name'];
				$changed = true;
			}
		}
		if($hide_scores)
		{
			$ctemp = array();
			foreach($client_info_dec as $a=>$v)
			{
				if(substr($a,0,1)!='_') $ctemp[$a] = $v;
			}
			$client_info_dec = $ctemp;
			$changed = true;
		}
		if($pack_again)
		{
			if($changed)
			{
				$client_info = array();
				foreach($client_info_dec as $a=>$v)
				{
					$client_info[$a] = liveadmin_encode64($v);
				}
				return(serialize($client_info));
			}
			else
			{
				return($client_info);
			}
		}
		return($client_info_dec);
	}
	function OutWaitingClients()
	{
		switch($_REQUEST['mode'])
		{
			case 'list': $list = $this->GetWaitingClients();
			$RES = array();
			$RES['status'] = 1;
			$RES['list'] = array();
			$sound=0;
			$real = intval(RealTime());
			$active_ips = array();
			$departments = lv_get_departments($_SERVER['sinfo']);
			foreach($list as $a=>$v)
			{
				if($v['client_flag']==300 && $_SERVER['uinfo']['show_missed_calls']==0) continue;
				$client_info_dec = liveadmin_decode64(unserialize($v['client_info']));
				if(isset($client_info_dec['_lv_department']))
				{
					$depid = $client_info_dec['_lv_department'];
					if(count($departments)>0)
					{
						if(isset($departments[$depid]) && !isset($departments[$depid]['agents'][$_SERVER['uinfo']['userid']]) )
						{
							continue;
						}
					}
					if(isset($departments[$depid])) $client_info_dec['lv_department_res'] = $departments[$depid]['name'];
				}
				if($v['client_flag']==7)
				{
					if(isset($client_info_dec['_lv_caller_sid']) && $client_info_dec['_lv_caller_sid']!=$_REQUEST['lv_sid'] ) continue;
				}
				if(isset($client_info_dec['_lv_excl_userid']) && is_numeric($client_info_dec['_lv_excl_userid']) && $client_info_dec['_lv_excl_userid']>0)
				{
					if($client_info_dec['_lv_excl_userid']!=$_SERVER['uinfo']['userid']) continue;
				}
				$flag_x = 0;
				$flag_y = 0;
				$this->CountryToFlagPos($v['client_ip_country'],$flag_x,$flag_y);
				$out = array('client_uniq'=>$v['client_uniq'],'client_ip'=>$v['client_ip'],'client_ip_country'=>$v['client_ip_country'],'server_uniq'=>$v['server_uniq']);
				$out['flag_x'] = $flag_x;
				$out['flag_y'] = $flag_y;
				$out['client_nickname'] = $v['client_nickname'];
				$out['client_flag'] = $v['client_flag'];
				$out['client_info'] = $client_info_dec;
				if($v['chat_start_ts']>0) $out['stime'] = lv_date('H:i:s',$v['chat_start_ts'],$_SERVER['uinfo']['time_zone']);
				else $out['stime'] = lv_date('M j, H:i:s',intval($v['client_dtm']),$_SERVER['uinfo']['time_zone']);
				$dt = $real - intval($v['client_dtm']);
				if($dt>30 || $v['client_flag']>=100)
				{
					$out['client_signal'] = '-150px';
					$out['client_signal_p'] = '-----';
				}
				elseif($dt>20)
				{
					$out['client_signal'] = '-120px';
					$out['client_signal_p'] = '+----';
				}
				elseif($dt>15)
				{
					$out['client_signal'] = '-90px';
					$out['client_signal_p'] = '++---';
				}
				elseif($dt>10)
				{
					$out['client_signal'] = '-60px';
					$out['client_signal_p'] = '+++--';
				}
				elseif($dt>5)
				{
					$out['client_signal'] = '-30px';
					$out['client_signal_p'] = '++++-';
				}
				else
				{
					$out['client_signal'] = '0px';
					$out['client_signal_p'] = '+++++';
				}
				$out['wait_time_s'] = $dt;
				$dts = $_SERVER['sinfo']['wait_find_rep_s']-$dt;
				if($dts<0) $dts = 0;
				$out['act_sort'] = 0;
				$out['btn_ans'] = 'n';
				$out['btn_dis'] = 'n';
				switch($v['client_flag'])
				{
					case 0: $out['assign_to'] = '<s5lang>Getting Info</s5lang>';
					$active_ips[$v['client_ip']] = 1;
					break;
					case 1: $active_ips[$v['client_ip']] = 1;
					$out['assign_to'] = '<s5lang>Waiting for agent</s5lang> ('.$dts.'s)';
					$out['act_sort'] = 100;
					$out['btn_ans'] = 'y';
					$out['btn_dis'] = 'y';
					$sound=1;
					break;
					case 2: $active_ips[$v['client_ip']] = 1;
					$out['assign_to'] = '<s5lang>Taking message</s5lang>';
					break;
					case 3: $active_ips[$v['client_ip']] = 1;
					$out['assign_to'] = '<s5lang>Message Taken</s5lang>';
					break;
					case 4: $active_ips[$v['client_ip']] = 1;
					$out['assign_to'] = '<s5lang>Assigned to</s5lang> '.$v['server_nickname'];
					if($_SERVER['uinfo']['access_level']==0 || $v['server_userid']==$_SERVER['uinfo']['userid']) $out['btn_ans'] = 'y';
					break;
					case 5: $active_ips[$v['client_ip']] = 1;
					$out['assign_to'] = '<s5lang>Busy notice</s5lang>';
					break;
					case 6: $active_ips[$v['client_ip']] = 1;
					$out['assign_to'] = '<s5lang>Hold on</s5lang> '.$v['server_nickname'];
					if($_SERVER['uinfo']['access_level']==0 || $v['server_userid']==$_SERVER['uinfo']['userid']) $out['btn_ans'] = 'y';
					break;
					case 7: $active_ips[$v['client_ip']] = 1;
					$out['assign_to'] = '<s5lang>Direct chat request</s5lang>';
					break;
					case 100: $out['assign_to'] = '<s5lang>Chat closed by client</s5lang>';
					$out['act_sort'] = -100;
					break;
					case 101: $out['assign_to'] = '<s5lang>Chat closed by agent</s5lang>';
					$out['act_sort'] = -100;
					break;
					case 200: $out['assign_to'] = '<s5lang>Archiving...</s5lang>';
					$out['act_sort'] = -200;
					break;
					case 201: $out['assign_to'] = '<s5lang>Archived</s5lang>';
					$out['act_sort'] = -200;
					break;
					case 300: $out['assign_to'] = '<s5lang>Missed Call</s5lang>';
					$out['act_sort'] = -300;
					break;
				}
				$RES['list'][] = $out;
			}
			$RES['count'] = count($RES['list']);
			usort($RES['list'],array($this,'_get_waiting_client_sort'));
			$RES['sound'] = ($_SERVER['uinfo']['sound_new_client']=='--none--')?'0':$sound;
			if(LIVEADMIN_STANDALONE)
			{
				$RES['ver_info'] = array();
				$RES['ver_info']['show'] = lv_version_string();
				$RES['ver_info']['version'] = LIVEADMIN_VERSION;
				if(LIVEADMIN_LITE)
				{
					$RES['ver_info']['in_trial'] = 0;
					$RES['ver_info']['trial_mode'] = 'no';
				}
				else
				{
					$RES['ver_info']['in_trial'] = $_SERVER['sinfo']['in_trial'];
					$RES['ver_info']['trial_mode'] = ($_SERVER['sinfo']['in_trial']==1)?'yes':'no';
				}
			}
			$RES['notes'] = array();
			$RES['notes_count'] = 0;
			$show_renew_link = false;
			if($_SERVER['sinfo']['demo_mode']==1)
			{
				$RES['notes'][] = '<s5lang>Account is in DEMO mode.</s5lang>';
			}
			if($_SERVER['sinfo']['in_trial']==1)
			{
				if(LIVEADMIN_STANDALONE)
				{
					$RES['notes'][] = '<s5lang>Unregistered copy.</s5lang>';
				}
				$show_renew_link = true;
			}
			if($_SERVER['sinfo']['expiry_date']<time())
			{
				$_REQUEST['online_status'] = 'no';
				$RES['notes'][] = '<strong><s5lang>NOTE</s5lang>: <s5lang>Account has expired</s5lang>. <s5lang>It remains in offline mode until you renew the account.</s5lang></strong>';
				$show_renew_link = true;
				if($_SERVER['sinfo']['expiry_date']-time()<(30*24*3600))
				{
					$RES['notes'][] = '<strong><s5lang>Account will be removed from server in</s5lang> '.TimeDiffText($_SERVER['sinfo']['expiry_date']-time()+(30*24*3600)).'</strong>';
				}
				else
				{
					$RES['notes'][] = '<strong><s5lang>Pending removal from server.</s5lang></strong>';
				}
			}
			elseif($_SERVER['sinfo']['expiry_date']<time()+(3*24*3600))
			{
				$RES['notes'][] = '<strong><s5lang>NOTE</s5lang>: <s5lang>Account will expire in</s5lang> '.TimeDiffText($_SERVER['sinfo']['expiry_date']-time()).'.</strong>';
				$show_renew_link = true;
			}
			elseif($_SERVER['sinfo']['expiry_date']<time()+(15*24*3600))
			{
				$RES['notes'][] = '<strong><s5lang>Account will expire in</s5lang> '.TimeDiffText($_SERVER['sinfo']['expiry_date']-time()).'.</strong>';
				$show_renew_link = true;
			}
			elseif($_SERVER['sinfo']['expiry_date']<time()+(30*24*3600))
			{
				$RES['notes'][] = '<s5lang>Account will expire in</s5lang> '.TimeDiffText($_SERVER['sinfo']['expiry_date']-time()).'.';
				$show_renew_link = true;
			}
			if($_REQUEST['online_status']=='no')
			{
				$RES['notes'][] = '<s5lang>Account is in offline mode.</s5lang>';
			}
			if($show_renew_link && $_SERVER['uinfo']['access_level']==0)
			{
				if(LIVEADMIN_STANDALONE)
				{
					$RES['notes'][] = '<span class="renew_link" onclick="ConfigGeneralClick(11);"><s5lang>Register Now</s5lang></span>';
				}
			}
			$RES['notes_count'] = count($RES['notes']);
			if($_SERVER['sinfo']['expiry_date']<time()) $RES['can_online'] = 'no';
			else $RES['can_online'] = 'yes';
			if($_SERVER['uinfo']['access_level']==0 || $_SERVER['uinfo']['can_see_agents']==1)
			{
				$RES['online_agents'] = array();
				$OnlineAgents = $this->GetOnlineAgents(true);
				$OnlineAgents = $this->SortOnlineAgents($OnlineAgents);
				$RES['online_agents_count'] = 0;
				foreach($OnlineAgents as $agent)
				{
					$access_level = '';
					$agent_icon = 'a';
					switch($agent['access_level'])
					{
						case 0: $access_level = '<s5lang>Root</s5lang>';
						$agent_icon .= '0';
						break;
						case 1: $access_level = '<s5lang>Agent</s5lang>';
						$agent_icon .= '1';
						break;
					}
					$is_self = ($_SERVER['uinfo']['userid']==$agent['userid'])?'y':'n';
					$agent_online = ($agent['agent_online'])?'y':'n';
					$agent_status = ($agent['ac_status']==1)?'a':'s';
					if($agent_status=='s')
					{
						$agent_icon .= 's';
					}
					else
					{
						if($agent_online=='y') $agent_icon .= '1';
						else $agent_icon .= '0';
					}
					$RES['online_agents'][] = array($agent['userid'],$agent['nickname'],$access_level,$is_self,$agent_online,$agent_status,$agent_icon);
					$RES['online_agents_count']++;
				}
			}
			if($_SERVER['uinfo']['access_level']==0 || $_SERVER['uinfo']['can_see_visitors']==1)
			{
				if(ArrayMember($_REQUEST,'gv')=='y' || ArrayMember($_REQUEST,'gv')=='ay')
				{
					$RES['visitors'] = array();
					$Visitors = $this->GetVisitors();
					$RES['visitors_count'] = 0;
					$RES['visitors_type'] = ArrayMember($_REQUEST,'gv');
					foreach($Visitors as $per)
					{
						if($per['ip']=='') continue;
						$flag_x = 0;
						$flag_y = 0;
						$this->CountryToFlagPos($per['ip_country'],$flag_x,$flag_y);
						if(isset($active_ips[$per['ip']])) $ip_active = 'y';
						else $ip_active = 'n';
						$page = ArrayMember($per,'page_tag','');
						if($page=='')
						{
							$page = URL2View($per['url']);
						}
						if(ArrayMember($_REQUEST,'gv')=='y')
						{
							$RES['visitors'][] = array($per['ip'],$page,$flag_x,$flag_y,$ip_active);
						}
						if(ArrayMember($_REQUEST,'gv')=='ay')
						{
							$RES['visitors'][] = array($per['ip'],$page,$flag_x,$flag_y,$ip_active,$per['ip_country'],$per['browser'],$per['ip_x'],$per['ip_y'],$per['url']);
						}
					}
					$RES['visitors_count'] = count($RES['visitors']);
				}
			}
			$RES['msg_intern'] = array();
			$RES['msg_intern_count'] = 0;
			if($_SERVER['uinfo']['check_msg_intern']>time()-(10*60))
			{
				$RES['msg_intern'] = $this->GetMessageInterns();
			}
			elseif($_SERVER['uinfo']['check_msg_intern']==0)
			{
				$this->ResetInternCheck(true);
				$RES['msg_intern'] = $this->GetMessageInterns();
			}
			$RES['msg_intern_count'] = count($RES['msg_intern']);
			$RES['lv_version'] = LIVEADMIN_VERSION;
			PrintJsonPack($RES);
			break;
		}
	}
	function _get_waiting_client_sort($a,$b)
	{
		if($a['act_sort']>$b['act_sort']) return(-1);
		elseif($a['act_sort']<$b['act_sort']) return(1);
		else return(($a['wait_time_s']<$b['wait_time_s'])?-1:1);
	}
	function StartChat($client_uniq)
	{
		$RES = array();
		$cinfo = $this->GetClientInfo($client_uniq);
		$this->client_uniq = $client_uniq;
		if($cinfo===false)
		{
			$RES['client_uniq'] = $this->client_uniq;
			$RES['status'] = 0;
		}
		elseif($cinfo['server_userid']==0)
		{
			$client_info_dec = liveadmin_decode64(unserialize($cinfo['client_info']));
			if(isset($client_info_dec['_lv_server_uniq']) && $client_info_dec['_lv_server_uniq']!='')
			{
				$this->server_uniq = $client_info_dec['_lv_server_uniq'];
			}
			else
			{
				$this->server_uniq = RandomString(32);
			}
			$chat_start_ts = $this->TakeOwnershipOfClient($this->client_uniq);
			$this->SendInitMessagesToClient($this->client_uniq);
			$RES['status'] = 1;
			$RES['server_uniq'] = $this->server_uniq;
			$RES['client_uniq'] = $this->client_uniq;
			$RES['client_ip'] = $cinfo['client_ip'];
			$RES['client_flag'] = $cinfo['client_flag'];
			$RES['client_info'] = $client_info_dec;
			$RES['start_timestamp'] = $chat_start_ts;
			$RES['current_timestamp'] = time();
			include_once('geoip.php');
			$geoip = new LV_Geoip();
			$RES['client_ip_extra'] = $geoip->GetInfoString($cinfo['client_ip'],'region_city_country');
			unset($geoip);
		}
		elseif($cinfo['server_userid']!=$_SERVER['uinfo']['userid'])
		{
			$RES['client_uniq'] = $this->client_uniq;
			$RES['client_info'] = liveadmin_decode64(unserialize($cinfo['client_info']));
			$RES['client_ip'] = $cinfo['client_ip'];
			$RES['server_uniq'] = $cinfo['server_uniq'];
			$RES['client_flag'] = $cinfo['client_flag'];
			$RES['start_timestamp'] = $cinfo['chat_start_ts'];
			$RES['current_timestamp'] = time();
			$RES['status'] = 2;
		}
		else
		{
			$this->server_uniq = $cinfo['server_uniq'];
			$RES['status'] = 1;
			$RES['server_uniq'] = $this->server_uniq;
			$RES['client_uniq'] = $this->client_uniq;
			$RES['client_ip'] = $cinfo['client_ip'];
			$RES['client_info'] = liveadmin_decode64(unserialize($cinfo['client_info']));
			$RES['client_flag'] = $cinfo['client_flag'];
			$RES['start_timestamp'] = $cinfo['chat_start_ts'];
			$RES['current_timestamp'] = time();
			include_once('geoip.php');
			$geoip = new LV_Geoip();
			$RES['client_ip_extra'] = $geoip->GetInfoString($cinfo['client_ip'],'region_city_country');
			unset($geoip);
		}
		$RES['client_info'] = $this->ClientInfo2Array($RES['client_info']);
		PrintJsonPack($RES);
	}
	function GetVisitors()
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'visitors';
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$res = mysql_query("SELECT * FROM $tbl WHERE siteid=$siteid_esc ORDER BY dtm DESC LIMIT 0,100 ",$dbh);
		$RV = array();
		$geo = false;
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV[] = $req;
				if($req['ip_country']=='')
				{
					$geo = true;
				}
			}
		}
		mysql_close($dbh);
		if($geo)
		{
			include_once('geoip.php');
			$geoip = new LV_Geoip();
			$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
			mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
			$tbl = LIVEADMIN_DB_PREFIX.'visitors';
			foreach($RV as $a=>$v)
			{
				if($v['ip_country']=='')
				{
					$rec = $geoip->GetRecord($v['ip']);
					$v['ip_country'] = $rec['country_code'];
					$v['ip_x'] = $rec['longitude']*1;
					$v['ip_y'] = $rec['latitude']*1;
					if($v['ip_x']<-180) $v['ip_x'] = -180;
					if($v['ip_x']>180) $v['ip_x'] = 180;
					if($v['ip_y']<-90) $v['ip_y'] = -90;
					if($v['ip_y']>90) $v['ip_y'] = 90;
					if($v['ip_country']=='') $v['ip_country'] = "--";
					mysql_query("UPDATE $tbl SET ip_country='".$v['ip_country']."', ip_x=".$v['ip_x'].", ip_y=".$v['ip_y']." WHERE siteid=$siteid_esc AND ip='".$v['ip']."'",$dbh);
					$RV[$a] = $v;
				}
			}
			unset($geoip);
			mysql_close($dbh);
		}
		return($RV);
	}
	function DiscardChat($client_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		switch($_SERVER['sinfo']['no_answer_act'])
		{
			default: case 0: $flag = 2;
			break;
			case 1: $flag = 5;
			break;
		}
		mysql_query("UPDATE $tbl SET client_flag='$flag'  WHERE client_uniq='$client_uniq_esc' ",$dbh);
		mysql_close($dbh);
	}
	function SetChatFlag($client_uniq,$server_uniq,$flag)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
		$flag_esc = mysql_real_escape_string($flag,$dbh);
		mysql_query("UPDATE $tbl SET client_flag='$flag_esc'  WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' ",$dbh);
		mysql_close($dbh);
	}
	function GetChatFlag($client_uniq,$server_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
		$res = mysql_query("SELECT client_flag FROM $tbl WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' ",$dbh);
		$RV = 0;
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV = $req['client_flag'];
				break;
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function LoadTheme()
	{
		$RV = '';
		if(!is_file(LIVEADMIN_T.'/'.$_SERVER['sinfo']['theme'].'/admin_chat.htm'))
		{
			$_SERVER['sinfo']['theme'] = LIVEADMIN_DEFAULT_THEME;
		}
		$RV = file_get_contents(LIVEADMIN_T.'/'.$_SERVER['sinfo']['theme'].'/admin_chat.htm');
		return($RV);
	}
	function TranslateTheme($page)
	{
		$page = preg_replace_callback('/<%value\s+(.*)%>/ismU',array(&$this,'_translate_theme_callback'), $page);
		return($page);
	}
	function _translate_theme_callback($m)
	{
		$id = $m[1];
		if(substr($id,0,6)=='sinfo_')
		{
			return($_SERVER['sinfo'][substr($id,6)]);
		}
		elseif($id=='server_uniq')
		{
			return($this->server_uniq);
		}
		elseif($id=='client_uniq')
		{
			return($this->client_uniq);
		}
		return('');
	}
	function ShowPage($page)
	{
		print $page;
	}
	function GetContentsPart($file,$tag)
	{
		$fc = file_get_contents($file);
		preg_match('/<%\s*sec-start\s+'.$tag.'\s*%>(.*)<%\s*sec-end\s+'.$tag.'\s*%>/ismU',$fc,$m);
		return($this->TranslateTheme($m[1]));
	}
	function TakeOwnershipOfClient($client_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($this->client_uniq,$dbh);
		$server_uniq_esc = mysql_real_escape_string($this->server_uniq,$dbh);
		$realtime = RealTime();
		$server_userid = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$server_nickname = mysql_real_escape_string($_SERVER['uinfo']['nickname'],$dbh);
		$server_auto_link = mysql_real_escape_string($_SERVER['uinfo']['auto_link'],$dbh);
		$chat_start_ts = time();
		mysql_query("UPDATE $tbl SET server_uniq='$server_uniq_esc', server_dtm='$realtime', server_userid=$server_userid, server_nickname='$server_nickname', server_auto_link='$server_auto_link', chat_start_ts='$chat_start_ts'  WHERE client_uniq='$client_uniq_esc' ",$dbh);
		mysql_close($dbh);
		return($chat_start_ts);
	}
	function SendInitMessagesToClient($client_uniq)
	{
		$msgs = array ( trim($_SERVER['uinfo']['send_init_1']), trim($_SERVER['uinfo']['send_init_2']), trim($_SERVER['uinfo']['send_init_3']) );
		foreach($msgs as $message)
		{
			if($message!='') $this->PostNewMessage($message,$client_uniq,$this->server_uniq);
		}
	}
	function PostNewMessage($message,$client_uniq,$server_uniq,$direction=1)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg';
		$message = CheckEnc($message);
		if(!lv_is_utf8($message)) $message = lv_convert_utf8($message);
		$message = NoMultiLine($message);
		$message = StripHTML($message);
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
		$realtime = RealTime();
		$msg1 = mysql_real_escape_string(substr($message,0,250),$dbh);
		$msg2 = mysql_real_escape_string(substr($message,250),$dbh);
		mysql_query("INSERT INTO $tbl SET client_uniq='$client_uniq_esc', server_uniq='$server_uniq_esc', dtm='$realtime', direction='$direction', message_1='$msg1', message_2='$msg2' ",$dbh);
		mysql_close($dbh);
	}
	function ResetInternCheck($first_login)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		if($first_login) mysql_query("UPDATE $tbl SET check_msg_intern=0 WHERE userid=$userid_esc ",$dbh);
		else mysql_query("UPDATE $tbl SET check_msg_intern=1 WHERE userid=$userid_esc AND check_msg_intern=0",$dbh);
		mysql_close($dbh);
	}
	function PostNewInternMessage($message,$userid)
	{
		$message = CheckEnc($message);
		if(!lv_is_utf8($message)) $message = lv_convert_utf8($message);
		$message = NoMultiLine($message);
		$message = StripHTML($message);
		if(Trim($message)=='')
		{
			return(array('status'=>0));
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg_intern';
		$userid_esc = mysql_real_escape_string($userid,$dbh);
		$sender_userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$sender_nickname_esc = mysql_real_escape_string($_SERVER['uinfo']['nickname'],$dbh);
		$realtime = RealTime();
		$msg1 = mysql_real_escape_string(substr($message,0,250),$dbh);
		$msg2 = mysql_real_escape_string(substr($message,250),$dbh);
		mysql_query("INSERT INTO $tbl SET userid='$userid_esc', sender_userid='$sender_userid_esc', sender_nickname='$sender_nickname_esc', dtm='$realtime', message_1='$msg1', message_2='$msg2' ",$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$dtm = time();
		mysql_query("UPDATE $tbl SET check_msg_intern=$dtm WHERE userid=$userid_esc ",$dbh);
		mysql_close($dbh);
		$RV = array('status'=>1,'message'=>$message,'time'=>lv_date("H:i:s",time(),$_SERVER['uinfo']['time_zone']));
		return($RV);
	}
	function GetMessageInterns()
	{
		$RV = array();
		$uc = rand(100,30000);
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg_intern';
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		mysql_query("UPDATE $tbl SET status=$uc WHERE userid='$userid_esc' AND status=0 ",$dbh);
		if(mysql_affected_rows($dbh)>0)
		{
			$res = mysql_query("SELECT * FROM $tbl WHERE userid='$userid_esc' AND status=$uc ORDER BY dtm ",$dbh);
			if($res)
			{
				while($req = mysql_fetch_array($res))
				{
					$dtmd = intval($req['dtm']);
					if(lv_date("Y-M-d",$dtmd,$_SERVER['uinfo']['time_zone']) != lv_date("Y-M-d",time(),$_SERVER['uinfo']['time_zone']) ) $dtt = lv_date("Y-M-d H:i:s",$dtmd,$_SERVER['uinfo']['time_zone']);
					else $dtt = lv_date("H:i:s",$dtmd,$_SERVER['uinfo']['time_zone']);
					$RV[] = array ( 'sender_userid' => $req['sender_userid'], 'sender_nickname' => $req['sender_nickname'], 'dtm' => $req['dtm'], 'dtt' => $dtt, 'message' => $req['message_1'].$req['message_2'] );
				}
			}
			mysql_query("DELETE FROM $tbl WHERE userid='$userid_esc' AND status=$uc ",$dbh);
		}
		mysql_close($dbh);
		return($RV);
	}
	function PostNewHistory($message)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg_hist';
		$message = CheckEnc($message);
		if(!lv_is_utf8($message)) $message = lv_convert_utf8($message);
		$message = NoMultiLine($message);
		$message = StripHTML($message);
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$msg_tag = mysql_real_escape_string(substr($message,0,20),$dbh);
		$message_esc = mysql_real_escape_string($message,$dbh);
		$msg_md5 = md5($message);
		$edate = time();
		mysql_query("INSERT INTO $tbl SET userid='$userid_esc', msg_md5='$msg_md5', message_tag='$msg_tag', message='$message_esc' ",$dbh);
		mysql_query("UPDATE $tbl SET edate='$edate' WHERE userid='$userid_esc' AND msg_md5='$msg_md5' ",$dbh);
		mysql_close($dbh);
	}
	function CleanupHistory()
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg_hist';
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$edate = time()-(30*24*3600);
		if($_SERVER['sinfo']['demo_mode']==1) $edate = time()-(1*24*3600);
		if($_SERVER['sinfo']['in_trial']==1) $edate = time()-(2*3600);
		mysql_query("DELETE FROM $tbl WHERE userid='$userid_esc' AND edate<$edate ",$dbh);
		mysql_close($dbh);
	}
	function DeleteHistory($query_md5)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg_hist';
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$msg_md5 = mysql_real_escape_string($query_md5,$dbh);
		mysql_query("DELETE FROM $tbl WHERE userid='$userid_esc' AND msg_md5='$msg_md5' ",$dbh);
		mysql_close($dbh);
	}
	function GetAutoComplete()
	{
		if(isset($_REQUEST['query_enc']))
		{
			$query = liveadmin_decode64($_REQUEST['query_enc']);
		}
		elseif(isset($_REQUEST['query']))
		{
			$query = $_REQUEST['query'];
		}
		else
		{
			return(array());
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg_hist';
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$query_esc = mysql_real_escape_string($query,$dbh).'%';
		$res = mysql_query("SELECT message, edate, message_tag FROM $tbl WHERE userid='$userid_esc' AND message_tag LIKE '$query_esc' ORDER BY edate DESC, message_tag LIMIT 0,20 ",$dbh);
		$RV = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV[] = $req['message'];
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function GetMessageLoop($client_uniq,$server_uniq,$last_check)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
		$last_check_esc = mysql_real_escape_string($last_check,$dbh);
		$realtime = RealTime();
		mysql_query("UPDATE $tbl SET server_dtm='$realtime' WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' ",$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg';
		$res = mysql_query("SELECT * FROM $tbl WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' AND dtm>$last_check_esc AND dtm<=$realtime ORDER BY dtm ",$dbh);
		$sound = 0;
		$RV = array();
		$RV['last_check'] = 'LC'.$realtime;
		$RV['status'] = 1;
		$RV['msg_count'] = 0;
		$RV['msgs'] = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$m = array('msg'=>$req['message_1'].$req['message_2'],'time'=>lv_date('H:i:s',intval($req['dtm']),$_SERVER['uinfo']['time_zone']));
				switch($req['direction'])
				{
					case 1: $m['dir'] = 's2c';
					if($_SERVER['uinfo']['auto_link'] == 1) $m = AutoLink($m);
					$RV['msgs'][$req['dtm'].rand(1000,9999)] = $m;
					$RV['msg_count']++;
					break;
					case 2: $m['dir'] = 'c2s';
					if($_SERVER['uinfo']['auto_link'] == 1) $m = AutoLink($m);
					$RV['msgs'][$req['dtm'].rand(1000,9999)] = $m;
					$RV['msg_count']++;
					$sound = 2;
					break;
					case 3: $m['dir'] = 'svc';
					$RV['msgs'][$req['dtm'].rand(1000,9999)] = $m;
					$RV['msg_count']++;
					break;
					case 5: $m['dir'] = 'ctl';
					$RV['msgs'][$req['dtm'].rand(1000,9999)] = $m;
					$RV['msg_count']++;
					break;
				}
			}
		}
		mysql_close($dbh);
		$RV['sound'] = ($_SERVER['uinfo']['sound_new_message']=='--none--')?'0':$sound;
		return($RV);
	}
	function GetClientInfo($client_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$res = mysql_query("SELECT * FROM $tbl WHERE client_uniq='$client_uniq_esc' AND client_info_set=1 ",$dbh);
		$RV = false;
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
	function ArchiveCheckDB($tbl)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl_proto = LIVEADMIN_DB_PREFIX.'chat_arch_proto';
		mysql_query("CREATE TABLE IF NOT EXISTS $tbl LIKE $tbl_proto",$dbh);
		mysql_close($dbh);
	}
	function ArchiveChat($client_uniq,$server_uniq)
	{
		if($_SERVER['sinfo']['demo_mode']==1) return false;
		if($server_uniq=='')
		{
			return;
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$res = mysql_query("SELECT * FROM $tbl WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' ",$dbh);
		$wait_rec = false;
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$wait_rec = $req;
				break;
			}
		}
		if($wait_rec===false)
		{
			mysql_close($dbh);
			return false;
		}
		$e_client_ip = $wait_rec['client_ip'];
		$e_client_nickname = $wait_rec['client_nickname'];
		$e_client_info = $wait_rec['client_info'];
		$e_server_siteid = Key2ID($wait_rec['site_key'])*1;
		$e_server_userid = $wait_rec['server_userid']*1;
		if($e_server_siteid<1 || $e_server_userid<1)
		{
			mysql_close($dbh);
			return false;
		}
		$e_client_info = $this->ClientInfo2Array($e_client_info,true,true);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		mysql_query("UPDATE $tbl SET client_flag=200 WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' ",$dbh);
		$e_start_date = 9999999999;
		$e_end_date = 0;
		$e_msg_count = 0;
		$tbl = LIVEADMIN_DB_PREFIX.'msg';
		$res = mysql_query("SELECT * FROM $tbl WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' AND direction<=4 ORDER BY dtm ",$dbh);
		$e_message = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$dt = intval($req['dtm']);
				if($dt<$e_start_date) $e_start_date = $dt;
				if($dt>$e_end_date) $e_end_date = $dt;
				$e_message_temp = array($req['direction'],$dt,$req['message_1'].$req['message_2']);
				if(isset($req['msg_userid']) && $req['msg_userid']>0)
				{
					$e_message_temp[3] = $req['msg_userid'];
				}
				$e_message[] = $e_message_temp;
				$e_msg_count++;
			}
		}
		if($e_end_date==0)
		{
			$e_start_date = intval($wait_rec['client_dtm']);
			$e_end_date = intval($wait_rec['client_dtm']);
		}
		$tbl = LIVEADMIN_DB_PREFIX.'chat_arch_'.$e_server_siteid;
		$this->ArchiveCheckDB($tbl);
		$res = mysql_query("SELECT MAX(chatid) as chatid_max FROM $tbl ",$dbh);
		$chatid = 100;
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$chatid = max($chatid,$req['chatid_max']);
				break;
			}
		}
		$chatid+=rand(1,10);
		$e_siteid = $e_server_siteid;
		$e_userid = $e_server_userid;
		$e_message = liveadmin_encode64(serialize($e_message));
		$SQL = array ( "userid='$e_userid'", "siteid='$e_siteid'", "chatid='$chatid'", "start_date='$e_start_date'", "end_date='$e_end_date'", "msg_count='$e_msg_count'", "client_nickname='$e_client_nickname'", "client_ip='$e_client_ip'", "client_info='$e_client_info'", "message='$e_message'" );
		mysql_query("INSERT INTO $tbl SET ".implode(",",$SQL),$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg';
		mysql_query("DELETE FROM $tbl WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' ",$dbh);
		$dtm_cleanup = RealTime()-(7*24*3600);
		mysql_query("DELETE FROM $tbl WHERE dtm<$dtm_cleanup ",$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		mysql_query("UPDATE $tbl SET client_flag=201 WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_esc' ",$dbh);
		$dtm_limit = RealTime()-3600;
		mysql_query("DELETE FROM $tbl WHERE client_flag=201 AND (client_dtm<$dtm_limit OR server_dtm<$dtm_limit)",$dbh);
		mysql_close($dbh);
		return true;
	}
	function GetLogList($r)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'chat_arch_'.$_SERVER['sinfo']['siteid'];
		$start = lv_mktime(0,0,0,$r['sdm'],$r['sdd'],$r['sdy'],$_SERVER['uinfo']['time_zone']);
		$end = lv_mktime(23,59,59,$r['edm'],$r['edd'],$r['edy'],$_SERVER['uinfo']['time_zone']);
		$start = mysql_real_escape_string($start,$dbh);
		$end = mysql_real_escape_string($end,$dbh);
		$siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$ex_cond = '';
		if($_SERVER['uinfo']['access_level']!=0)
		{
			$ex_cond = " AND userid='".mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh)."' ";
		}
		$res = mysql_query("SELECT chatid,start_date,end_date,client_nickname,msg_count FROM $tbl WHERE siteid='$siteid' AND (start_date BETWEEN $start AND $end OR end_date BETWEEN $start AND $end) ".$ex_cond." LIMIT 0,9999",$dbh);
		$RV = array();
		$RV['status'] = 1;
		$RV['count'] = 0;
		$RV['list'] = array();
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$len = $req['end_date']-$req['start_date'];
				if($len<0)$len=0;
				$RV['list'][] = array ( 'chatid' => $req['chatid'], 'start_date'=> $req['start_date']*1, 'end_date'=> $req['end_date']*1, 'msg_count'=> $req['msg_count']*1, 'len'=>$len*1, 'len_text'=>Second2Text($len), 'sd_text'=> lv_date("M j, Y G:i:s",$req['start_date'],$_SERVER['uinfo']['time_zone']), 'ed_text'=> lv_date("M j, Y G:i:s",$req['end_date'],$_SERVER['uinfo']['time_zone']), 'client_nickname'=> $req['client_nickname'] );
				$RV['count']++;
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function GetLogMsg($r)
	{
		$RV = array();
		$RV['status']=0;
		if(!isset($r['chatid'])) return($RV);
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'chat_arch_'.$_SERVER['sinfo']['siteid'];
		$chatid = mysql_real_escape_string($r['chatid'],$dbh);
		$siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$ex_cond = '';
		if($_SERVER['uinfo']['access_level']!=0)
		{
			$ex_cond = " AND userid='".mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh)."' ";
		}
		$res = mysql_query("SELECT userid, message, client_nickname, client_ip, client_info, start_date,end_date FROM $tbl WHERE siteid='$siteid' AND chatid='$chatid' ".$ex_cond." ",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$cinfo = liveadmin_decode64(unserialize($req['client_info']));
				$agent_info = GetUserInfo('userid',$req['userid']);
				$RV['result'] = '== <s5lang>Client Information</s5lang> =='."\n";
				$RV['result'] .= '<s5lang>Start Date</s5lang>: '.lv_date("r",$req['start_date'],$_SERVER['uinfo']['time_zone'])."\n";
				$RV['result'] .= '<s5lang>End Date</s5lang>: '.lv_date("r",$req['end_date'],$_SERVER['uinfo']['time_zone'])."\n";
				$RV['result'] .= '<s5lang>Duration</s5lang>: '.Second2Text($req['end_date']-$req['start_date'])."\n";
				$RV['result'] .= '<s5lang>Client IP</s5lang>: '.$req['client_ip']."\n";
				$RV['result'] .= '<s5lang>Client Nickname</s5lang>: '.$req['client_nickname']."\n";
				$RV['result'] .= '<s5lang>Agent Nickname</s5lang>: '.$agent_info['nickname']." (#".$req['userid'].")\n";
				foreach($cinfo as $a=>$v)
				{
					if(substr($a,0,1)=='_') continue;
					if($a=='lv_department_res') $RV['result'] .= '<s5lang>Department</s5lang>: '.$v."\n";
					else $RV['result'] .= $a.': '.$v."\n";
				}
				$RV['result'] .= "\n\n";
				$RV['result'] .= '== <s5lang>Chat Transcript</s5lang> =='."\n";
				$msg = lv_unserialize(liveadmin_decode64($req['message']));
				$message = '';
				foreach($msg as $a=>$v)
				{
					switch($v[0])
					{
						case 1: $dir = '<=';
						break;
						case 2: $dir = '=>';
						break;
						case 3: $dir = '~>';
						break;
						case 4: $dir = '<~';
						break;
					}
					$date = lv_date("H:i:s",$v[1],$_SERVER['uinfo']['time_zone']);
					if(isset($v[3]) && $v[3]>0)
					{
						$trand_uinfo = GetUserInfo('userid',$v[3]);
						if($trand_uinfo===false) $trans_nickname = '#'.$v[3];
						else $trans_nickname = $trand_uinfo['nickname'];
						$message .= $dir.' '.$date.' ['.$trans_nickname.'] '.$v[2]."\n";
					}
					else $message .= $dir.' '.$date.' '.$v[2]."\n";
				}
				if($message=="" && $req['start_date']==$req['end_date'])
				{
					$message .= "***"."<s5lang>Missed Call</s5lang>"."***\n";
				}
				$RV['result'] = liveadmin_encode64($RV['result'].$message);
				$RV['status'] = 1;
				break;
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function DeleteLog($r)
	{
		$RV = array();
		$RV['status']=0;
		if(!isset($r['chatid']) || $r['chatid']=='') return($RV);
		if(strpos($r['chatid'],",")!==false)
		{
			$chatid = explode(",",$r['chatid']);
		}
		else
		{
			$chatid = array($r['chatid']);
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'chat_arch_'.$_SERVER['sinfo']['siteid'];
		$chatid_cond = array();
		foreach($chatid as $a=>$v)
		{
			$chatid_cond[] = " chatid='".mysql_real_escape_string($v,$dbh)."' ";
		}
		$chatid_cond = implode("OR",$chatid_cond);
		$siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$ex_cond = '';
		if($_SERVER['uinfo']['access_level']!=0)
		{
			$ex_cond = " AND userid='".mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh)."' ";
		}
		mysql_query("DELETE FROM $tbl WHERE siteid='$siteid' AND ($chatid_cond) $ex_cond ",$dbh);
		mysql_close($dbh);
		$RV['status']=1;
		return($RV);
	}
	function DoReg()
	{
		if(!LIVEADMIN_STANDALONE)
		{
			$RV = array('status'=>0,'error'=>'12525');
			return($RV);
		}
		$RV = array('status'=>0,'error'=>'12525');
		return($RV);
	}
	function CheckUpgrade()
	{
		$RV = array();
		$RV['status'] = 0;
		$ext_param = '';
		if(LIVEADMIN_LITE) $ext_param .= '&subv=lite';
		$header[] = "Accept: text/vnd.wap.wml,*.*";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.liveadmin.net/latest_version_standalone.php?rnd=".rand(10000,99999).$ext_param);
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
		if(!preg_match('/----START----(.*)----END----/ismU',$data,$m))
		{
			return($RV);
		}
		$verinfo = explode(':',$m[1]);
		if(count($verinfo)<2) return($RV);
		$latest_version = $verinfo[0];
		$latest_date = $verinfo[1];
		$RV = array();
		$RV['status'] = 1;
		$RV['upgrade'] = 0;
		$RV['installed_version'] = LIVEADMIN_VERSION;
		$RV['latest_version'] = $latest_version;
		if(version_compare($RV['installed_version'],$RV['latest_version'],'<'))
		{
			$RV['upgrade'] = 1;
			$RV['upgrade_text'] = '<s5lang>A new version of LiveAdmin is available, would you like to update?</s5lang>';
			$RV['iv_text'] = '<s5lang>Installed Version</s5lang>';
			$RV['lv_text'] = '<s5lang>Latest Version</s5lang>';
		}
		return($RV);
	}
	function DoUpgrade()
	{
		$RV = array();
		$host = ArrayMember($_REQUEST,'ftp_host');
		$port = ArrayMember($_REQUEST,'ftp_port');
		$user = ArrayMember($_REQUEST,'ftp_user');
		$pass = ArrayMember($_REQUEST,'ftp_pass');
		$folder = ArrayMember($_REQUEST,'ftp_folder');
		include_once('update.php');
		$lv_update = new LV_LiveAdminUpdate();
		$res = $lv_update->CheckFTPLogin($host,$port,$user,$pass,$folder);
		if($res!='')
		{
			$RV['status']=2;
			$RV['info'] = $res;
		}
		else
		{
			$RV['status']=1;
			$RV['info'] = 'FTP connection confirmed, updating...<br>DO NOT CLOSE THIS WINDOW OR UPDATE WILL BE INTERRUPTED<br>';
		}
		return($RV);
	}
	function StartUpgrade()
	{
		$params = array ( 'ftp_host' => ArrayMember($_REQUEST,'ftp_host'), 'ftp_port' => ArrayMember($_REQUEST,'ftp_port'), 'ftp_user' => ArrayMember($_REQUEST,'ftp_user'), 'ftp_pass' => ArrayMember($_REQUEST,'ftp_pass'), 'ftp_folder' => ArrayMember($_REQUEST,'ftp_folder'), 'report_errors' => strtolower(ArrayMember($_REQUEST,'report_errors','y')) );
		ini_set('max_execution_time',20*60);
		include_once('cache.php');
		$cache = new LV_Cache();
		$params_save = $params;
		unset($params_save['ftp_pass']);
		$cache->SetCache('FTP_INFO',serialize($params_save));
		include_once('update.php');
		$lv_update = new LV_LiveAdminUpdate();
		$res = $lv_update->Start($params['ftp_host'],$params['ftp_port'],$params['ftp_user'],$params['ftp_pass'],$params['ftp_folder']);
		if($res===false && $params['report_errors']=='y')
		{
			$lv_update->ReportErrors();
		}
	}
	function LoopUpgrade()
	{
		include_once('update.php');
		$lv_install = new LV_LiveAdminUpdate();
		$RV = array();
		$RV['status'] = 1;
		$RV['list'] = $lv_install->GetResults();
		return($RV);
	}
	function ModifyUpgradeFlag()
	{
		$RV = array();
		$new_flag = strtolower(ArrayMember($_REQUEST,'no_upgrade_check',''));
		if($new_flag=='' || $new_flag!='y')
		{
			$RV['status']=0;
			return($RV);
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$server_siteid = mysql_real_escape_string($_SERVER['uinfo']['siteid'],$dbh);
		$server_userid = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$SQIN = "UPDATE $tbl SET check_update=0 WHERE siteid='$server_siteid' AND userid='$server_userid' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV['status']=1;
		return($RV);
	}
	function UpdateSitesPref($r,$dialog)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'sites';
		$mar = array();
		$mar_unset = array();
		foreach($r as $a=>$v)
		{
			if(preg_match('/^(.+)X_X(.+)$/',$a,$m))
			{
				if(!isset($mar[$m[1]])) $mar[$m[1]] = (isset($_SERVER['sinfo'][$m[1]]) && $_SERVER['sinfo'][$m[1]]!='')?unserialize($_SERVER['sinfo'][$m[1]]):array();
				$mar[$m[1]][$m[2]] = $v;
				$mar_unset[] = $a;
			}
		}
		foreach($mar_unset as $m)
		{
			unset($r[$m]);
		}
		include_once('dialog.php');
		$lv_dialog = new LV_Dialog($this);
		$dstruct = $lv_dialog->GetDialogsStruct();
		$SQL = array();
		foreach($dstruct[$dialog]['fields'] as $a=>$v)
		{
			if(isset($v['name']) && isset($r[$v['name']]) && !isset($v['no_database']))
			{
				if($_SERVER['sinfo']['in_trial']==1 && isset($v['trial_mode_value'])) $SQL[] = $v['name'].'="'.mysql_real_escape_string($v['trial_mode_value'],$dbh).'"';
				else $SQL[] = $v['name'].'="'.mysql_real_escape_string($r[$v['name']],$dbh).'"';
			}
		}
		if(count($mar)>0)
		{
			foreach($mar as $a=>$v) $SQL[] = $a.'="'.mysql_real_escape_string(serialize($v),$dbh).'"';
		}
		$SQL_STR = implode(",",$SQL);
		$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$SQIN = "UPDATE $tbl SET $SQL_STR WHERE siteid='$server_siteid' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV = array();
		$RV['status'] = 1;
		return($RV);
	}
	function UpdateUsersPref($r,$dialog)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		include_once('dialog.php');
		$lv_dialog = new LV_Dialog($this);
		$dstruct = $lv_dialog->GetDialogsStruct();
		$SQL = array();
		foreach($dstruct[$dialog]['fields'] as $a=>$v)
		{
			if(isset($v['name']) && isset($r[$v['name']]))
			{
				if($v['name']=='email') $r[$v['name']] = trim(strtolower($r[$v['name']]));
				$SQL[] = $v['name'].'="'.mysql_real_escape_string($r[$v['name']],$dbh).'"';
			}
		}
		$SQL_STR = implode(",",$SQL);
		$server_userid = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$SQIN = "UPDATE $tbl SET $SQL_STR WHERE userid='$server_userid' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV = array();
		$RV['status'] = 1;
		$RV['action'] = 'SoundObj.ReloadSounds();';
		return($RV);
	}
	function UpdatePasswordPref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		$RV['status'] = 0;
		if(strlen($r['current_password'])<5 || $_SERVER['uinfo']['password']!=crypt($r['current_password'],$_SERVER['uinfo']['password']))
		{
			$RV['error'] = '<s5lang>Current password is not correct</s5lang>';
		}
		elseif(strlen($r['new_password'])<5)
		{
			$RV['error'] = '<s5lang>New password should be at least 5 characters</s5lang>';
		}
		elseif($r['new_password']!=$r['new_password_c'])
		{
			$RV['error'] = '<s5lang>Password and confirmation should be the same</s5lang>';
		}
		else
		{
			$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
			mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
			$tbl = LIVEADMIN_DB_PREFIX.'users';
			$pass = crypt($r['new_password'],'$1$'.RandomString(9));
			$server_userid = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
			$pass_enc = mysql_real_escape_string($pass,$dbh);
			$SQIN = "UPDATE $tbl SET password='$pass_enc'  WHERE userid='$server_userid' ";
			mysql_query($SQIN,$dbh);
			mysql_close($dbh);
			$RV['status'] = 2;
			$RV['info'] = '<s5lang>Password has been changed successfully. Please login again.</s5lang>';
			$RV['action'] = 'LogoutPress();';
		}
		return($RV);
	}
	function UpdateAgentsAddPref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$r['email'] = trim(strtolower($r['email']));
		$r['username'] = trim(strtolower($r['username']));
		$RV = array();
		$RV['status'] = 0;
		if($_SERVER['uinfo']['access_level']>0)
		{
			$RV['error'] = '<s5lang>Insufficent permission to run this command.</s5lang>';
			return($RV);
		}
		$ulist = GetUsersList($_SERVER['sinfo']['siteid']);
		if($_SERVER['sinfo']['in_trial']==1 && count($ulist)>=2)
		{
			$RV['error'] = '<s5lang>Cannot add more than 1 agent when account is in trial mode</s5lang>';
			return($RV);
		}
		if(strlen($r['username'])<5 || strlen($r['username'])>50 || InputFilter($r['username'],LIVEADMIN_CON_D.LIVEADMIN_CON_L.'_')!=$r['username'])
		{
			$RV['error'] = '<s5lang>Username should be alpha numeric and between 5 to 50 characters</s5lang>';
		}
		elseif(strlen($r['new_password'])<5)
		{
			$RV['error'] = '<s5lang>New password should be at least 5 characters</s5lang>';
		}
		elseif($r['new_password']!=$r['new_password_c'])
		{
			$RV['error'] = '<s5lang>Password and confirmation should be the same</s5lang>';
		}
		elseif(strlen($r['email'])<2 || !IsValidEmailSyntax($r['email']))
		{
			$RV['error'] = '<s5lang>Please enter a valid email address.</s5lang>';
		}
		elseif($this->CanCreateUser($r['username'])==false)
		{
			$RV['error'] = '<s5lang>Username exists in our database, Please use a different username.</s5lang>';
		}
		if(LIVEADMIN_STANDALONE)
		{
			if(GetUserInfo('username',$r['username'])!==false)
			{
				$RV['error'] = 'Username exists in our database, Please use a different username.';
			}
			elseif(GetUserInfo('email',$r['email'])!==false)
			{
				$RV['error'] = 'Email exists in our database, please use a different email address';
			}
		}
		if(isset($RV['error'])) return($RV);
		$userid = $res['data']['max_userid']+rand(1,10);
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		if(LIVEADMIN_STANDALONE)
		{
			$userid = 1000;
			$res = mysql_query("SELECT MAX(userid) as max_userid FROM $tbl",$dbh);
			$RV = array();
			if($res)
			{
				while($req = mysql_fetch_array($res))
				{
					$userid = $req['max_userid'];
					break;
				}
			}
			$userid+=rand(1,5);
		}
		$pass = crypt($r['new_password'],'$1$'.RandomString(9));
		$username_esc = mysql_real_escape_string($r['username'],$dbh);
		$nickname_esc = mysql_real_escape_string($r['nickname'],$dbh);
		$firstname_esc = mysql_real_escape_string($r['firstname'],$dbh);
		$lastname_esc = mysql_real_escape_string($r['lastname'],$dbh);
		$email_esc = mysql_real_escape_string($r['email'],$dbh);
		$access_level_esc = mysql_real_escape_string($r['access_level'],$dbh);
		$ac_status_esc = mysql_real_escape_string($r['ac_status'],$dbh);
		$can_see_agents_esc = mysql_real_escape_string($r['can_see_agents'],$dbh);
		$can_see_visitors_esc = mysql_real_escape_string($r['can_see_visitors'],$dbh);
		$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$pass_enc = mysql_real_escape_string($pass,$dbh);
		$time_zone_esc = mysql_real_escape_string($r['time_zone'],$dbh);
		$gflags_enc = '';
		if(intval($r['hide_news'])==1) setGFlags($gflags_enc,LIVEADMIN_GFLAGS_HIDE_NEWS);
		else unsetGFlags($gflags_enc,LIVEADMIN_GFLAGS_HIDE_NEWS);
		$check_update = '';
		if(LIVEADMIN_STANDALONE)
		{
			$check_update = ' , check_update=1';
		}
		$SQIN = "INSERT INTO $tbl SET userid='$userid', siteid='$server_siteid', username='$username_esc', password='$pass_enc', nickname='$nickname_esc', firstname='$firstname_esc',lastname='$lastname_esc', email='$email_esc', access_level='$access_level_esc', ac_status='$ac_status_esc', time_zone='$time_zone_esc', can_see_agents='$can_see_agents_esc', can_see_visitors='$can_see_visitors_esc', gflags='$gflags_enc' $check_update ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV['status'] = 1;
		$RV['action'] = 'document.conf_dialogs["dialog_agents"].lv.AgentsReload()';
		return($RV);
	}
	function UpdateAgentsEditSinglePref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		$RV['status'] = 0;
		if($_SERVER['uinfo']['access_level']>0)
		{
			$RV['error'] = '<s5lang>Insufficent permission to run this command.</s5lang>';
			return($RV);
		}
		if($_SERVER['uinfo']['userid'] == $r['userid'])
		{
			$RV['error'] = '<s5lang>You can not edit yourself!</s5lang>';
			return($RV);
		}
		$mod_field = $r['mod_field'];
		switch($mod_field)
		{
			case 'access_level': $m_field = 'access_level';
			$m_value = $r['mod_value'];
			break;
			case 'ac_status': $m_field = 'ac_status';
			$m_value = $r['mod_value'];
			break;
			default: $RV['error'] = '<s5lang>Unsuported field</s5lang>';
			return($RV);
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$m_value_esc = mysql_real_escape_string($m_value,$dbh);
		$userid_esc = mysql_real_escape_string($r['userid'],$dbh);
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$SQIN = "UPDATE $tbl SET $m_field='$m_value_esc' WHERE userid='$userid_esc' AND siteid='$siteid_esc' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV['status'] = 1;
		return($RV);
	}
	function UpdateAgentsEditPref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$r['email'] = trim(strtolower($r['email']));
		$r['username'] = trim(strtolower($r['username']));
		$RV = array();
		$RV['status'] = 0;
		if($_SERVER['uinfo']['access_level']>0)
		{
			$RV['error'] = '<s5lang>Insufficent permission to run this command.</s5lang>';
			return($RV);
		}
		if($_SERVER['uinfo']['userid'] == $r['userid'])
		{
			$RV['error'] = '<s5lang>You can not edit yourself!</s5lang>';
			return($RV);
		}
		$set_password = false;
		if(strlen($r['username'])<5 || strlen($r['username'])>50 || InputFilter($r['username'],LIVEADMIN_CON_D.LIVEADMIN_CON_L.'_')!=$r['username'])
		{
			$RV['error'] = '<s5lang>Username should be alpha numeric and between 5 to 50 characters</s5lang>';
		}
		elseif($r['new_password']!='xxxxxx')
		{
			$set_password = true;
			if(strlen($r['new_password'])<5)
			{
				$RV['error'] = '<s5lang>New password should be at least 5 characters</s5lang>';
				return($RV);
			}
			elseif($r['new_password']!=$r['new_password_c'])
			{
				$RV['error'] = '<s5lang>Password and confirmation should be the same</s5lang>';
				return($RV);
			}
		}
		if(strlen($r['email'])<2 || !IsValidEmailSyntax($r['email']))
		{
			$RV['error'] = '<s5lang>Please enter a valid email address.</s5lang>';
			return($RV);
		}
		if($this->CanCreateUser($r['username'])==false)
		{
			$RV['error'] = '<s5lang>Username exists in our database, Please use a different username.</s5lang>';
			return($RV);
		}
		if(isset($RV['error'])) return($RV);
		if(LIVEADMIN_STANDALONE)
		{
			$ui = GetUserInfo('username',$r['username']);
			if($ui!==false && $ui['userid']!=$r['userid'])
			{
				$RV['error'] = 'Username exists in our database, Please use a different username.';
				return($RV);
			}
			$ui = GetUserInfo('email',$r['email']);
			if($ui!==false && $ui['userid']!=$r['userid'])
			{
				$RV['error'] = 'Email exists in our database, please use a different email address';
				return($RV);
			}
			$ui = GetUserInfo('userid',$r['userid']);
			if($ui==false || $ui['userid']!=$r['userid'])
			{
				$RV['error'] = 'Invalid usage. Please use admin panel to manage agents';
				return($RV);
			}
		}
		$cuinfo = GetUserInfo('username',$r['username']);
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$username_esc = mysql_real_escape_string($r['username'],$dbh);
		$nickname_esc = mysql_real_escape_string($r['nickname'],$dbh);
		$firstname_esc = mysql_real_escape_string($r['firstname'],$dbh);
		$lastname_esc = mysql_real_escape_string($r['lastname'],$dbh);
		$email_esc = mysql_real_escape_string($r['email'],$dbh);
		$access_level_esc = mysql_real_escape_string($r['access_level'],$dbh);
		$ac_status_esc = mysql_real_escape_string($r['ac_status'],$dbh);
		$time_zone_esc = mysql_real_escape_string($r['time_zone'],$dbh);
		$can_see_agents_esc = mysql_real_escape_string($r['can_see_agents'],$dbh);
		$can_see_visitors_esc = mysql_real_escape_string($r['can_see_visitors'],$dbh);
		$userid_esc = mysql_real_escape_string($r['userid'],$dbh);
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$pass_in = '';
		if($set_password)
		{
			$pass = crypt($r['new_password'],'$1$'.RandomString(9));
			$pass_enc = mysql_real_escape_string($pass,$dbh);
			$pass_in = ", password='$pass_enc'";
		}
		$gflags_enc = $cuinfo['gflags'];
		if(intval($r['hide_news'])==1) setGFlags($gflags_enc,LIVEADMIN_GFLAGS_HIDE_NEWS);
		else unsetGFlags($gflags_enc,LIVEADMIN_GFLAGS_HIDE_NEWS);
		$SQIN = "UPDATE $tbl SET username='$username_esc', nickname='$nickname_esc', firstname='$firstname_esc',lastname='$lastname_esc', email='$email_esc', access_level='$access_level_esc', ac_status='$ac_status_esc', time_zone='$time_zone_esc', can_see_agents='$can_see_agents_esc', can_see_visitors='$can_see_visitors_esc', gflags='$gflags_enc' $pass_in WHERE userid='$userid_esc' AND siteid='$siteid_esc' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV['status'] = 1;
		$RV['action'] = 'document.conf_dialogs["dialog_agents"].lv.AgentsReload()';
		return($RV);
	}
	function UpdateAgentsDeletePref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		if($_SERVER['uinfo']['access_level']>0)
		{
			$RV['error'] = '<s5lang>Insufficent permission to run this command.</s5lang>';
			return($RV);
		}
		if($_SERVER['uinfo']['userid'] == $r['userid'])
		{
			$RV['status'] = 0;
			$RV['error'] = '<s5lang>You can not delete yourself!</s5lang>';
			return($RV);
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$userid_esc = mysql_real_escape_string($r['userid'],$dbh);
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$SQIN = "DELETE FROM $tbl WHERE userid='$userid_esc' AND siteid='$siteid_esc' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$departments = $this->GetDepartmentsList();
		foreach($departments as $a=>$v)
		{
			if(isset($v['agents'][$r['userid']]))
			{
				unset($departments[$a]['agents'][$r['userid']]);
			}
		}
		$opts = $_SERVER['sinfo']['chat_window_options'];
		if($opts=='') $opts = array();
		else $opts = unserialize($opts);
		$opts['departments'] = $departments;
		$nopts = serialize($opts);
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'sites';
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$nopts_esc = mysql_real_escape_string($nopts,$dbh);
		$SQIN = "UPDATE $tbl SET chat_window_options='$nopts_esc' WHERE siteid='$siteid_esc' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'photo';
		$userid_esc = mysql_real_escape_string($r['userid'],$dbh);
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$SQIN = "DELETE FROM $tbl WHERE userid='$userid_esc' AND siteid='$siteid_esc' ";
		mysql_query($SQIN,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg_hist';
		$SQIN = "DELETE FROM $tbl WHERE userid='$userid_esc' ";
		mysql_query($SQIN,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'draft';
		$SQIN = "DELETE FROM $tbl WHERE userid='$userid_esc' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV['status'] = 1;
		$RV['action'] = 'document.conf_dialogs["dialog_agents"].lv.AgentsReload()';
		return($RV);
	}
	function UpdateStringsPref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$update = array();
		foreach($r as $a=>$v)
		{
			if(preg_match('/^texts_(.*)_(cb|text)$/isU',trim($a),$m))
			{
				if(!isset($update[$m[1]])) $update[$m[1]] = array();
				$update[$m[1]][$m[2]] = $v;
			}
		}
		$MID = array();
		foreach($update as $id=>$v)
		{
			if(strtoupper($v['cb'])!='ON') $MID[$id] = $v['text'];
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'sites';
		$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$chat_window_texts_esc = mysql_real_escape_string(serialize($MID),$dbh);
		$SQIN = "UPDATE $tbl SET chat_window_texts='$chat_window_texts_esc' WHERE siteid='$server_siteid' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV = array();
		$RV['status'] = 1;
		return($RV);
	}
	function UpdateDepartmentAddPref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		$RV['status'] = 0;
		$departments = $this->GetDepartmentsList();
		$r['name'] = trim($r['name']);
		if($_SERVER['sinfo']['in_trial']==1 && count($departments)>=2)
		{
			$RV['error'] = '<s5lang>Cannot add more than 2 departments when account is in trial mode</s5lang>';
		}
		elseif(strlen($r['name'])<2)
		{
			$RV['error'] = '<s5lang>Department name should be at least 2 characters</s5lang>';
		}
		else
		{
			$d_exists = false;
			foreach($departments as $a=>$v)
			{
				if(strtolower($v['name'])==strtolower($r['name']))
				{
					$RV['error'] = '<s5lang>Department exists, please delete it first or use a different name.</s5lang>';
					$d_exists = true;
					break;
				}
			}
			if($d_exists==false)
			{
				$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
				mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
				$tbl = LIVEADMIN_DB_PREFIX.'sites';
				$inf = array();
				$inf['name'] = $r['name'];
				$inf['agents'] = array();
				$agns = explode(',',$r['agents_list_added']);
				foreach($agns as $a=>$v)
				{
					$v = trim($v);
					if($v!='' && strlen($v)>1) $inf['agents'][$v] = array();
				}
				for($i=0;$i<100000;$i++)
				{
					if(!isset($departments[$i]))
					{
						$departments[$i] = $inf;
						break;
					}
				}
				$opts = $_SERVER['sinfo']['chat_window_options'];
				if($opts=='') $opts = array();
				else $opts = unserialize($opts);
				$opts['departments'] = $departments;
				$nopts = serialize($opts);
				$nopts_esc = mysql_real_escape_string($nopts,$dbh);
				$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
				$SQIN = "UPDATE $tbl SET chat_window_options='$nopts_esc' WHERE siteid='$server_siteid' ";
				mysql_query($SQIN,$dbh);
				mysql_close($dbh);
				$RV['status'] = 1;
				$RV['action'] = 'document.conf_dialogs["dialog_departments"].lv.DepartmentsReload()';
			}
		}
		return($RV);
	}
	function UpdateDepartmentEditPref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		$RV['status'] = 0;
		$departments = $this->GetDepartmentsList();
		$r['name'] = trim($r['name']);
		if($_SERVER['sinfo']['in_trial']==1 && count($departments)>=2)
		{
			$RV['error'] = '<s5lang>Cannot add more than 2 departments when account is in trial mode</s5lang>';
		}
		elseif(strlen($r['name'])<2)
		{
			$RV['error'] = '<s5lang>Department name should be at least 2 characters</s5lang>';
		}
		else
		{
			$d_exists = false;
			foreach($departments as $a=>$v)
			{
				if(strtolower($v['name'])==strtolower($r['name']) && $r['depid']!=$a)
				{
					$RV['error'] = '<s5lang>Department exists, please delete it first or use a different name.</s5lang>';
					$d_exists = true;
					break;
				}
			}
			if($d_exists==false)
			{
				$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
				mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
				$tbl = LIVEADMIN_DB_PREFIX.'sites';
				$inf = array();
				$inf['name'] = $r['name'];
				$inf['agents'] = array();
				$agns = explode(',',$r['agents_list_added']);
				foreach($agns as $a=>$v)
				{
					$v = trim($v);
					if($v!='' && strlen($v)>1) $inf['agents'][$v] = array();
				}
				$departments[$r['depid']] = $inf;
				$opts = $_SERVER['sinfo']['chat_window_options'];
				if($opts=='') $opts = array();
				else $opts = unserialize($opts);
				$opts['departments'] = $departments;
				$nopts = serialize($opts);
				$nopts_esc = mysql_real_escape_string($nopts,$dbh);
				$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
				$SQIN = "UPDATE $tbl SET chat_window_options='$nopts_esc' WHERE siteid='$server_siteid' ";
				mysql_query($SQIN,$dbh);
				mysql_close($dbh);
				$RV['status'] = 1;
				$RV['action'] = 'document.conf_dialogs["dialog_departments"].lv.DepartmentsReload()';
			}
		}
		return($RV);
	}
	function UpdateDepartmentDeletePref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		$RV['status'] = 0;
		$departments = $this->GetDepartmentsList();
		$d_exists = false;
		foreach($departments as $a=>$v)
		{
			if($r['depid']==$a)
			{
				$d_exists = true;
				break;
			}
		}
		if($d_exists==false)
		{
			$RV['error'] = '<s5lang>Department does not exist, please reload the department list dialog.</s5lang>';
		}
		elseif(count($departments[$r['depid']]['agents'])>0)
		{
			$RV['error'] = '<s5lang>Department has some agents, please remove them from this department first.</s5lang>';
		}
		else
		{
			$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
			mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
			$tbl = LIVEADMIN_DB_PREFIX.'sites';
			unset($departments[$r['depid']]);
			$opts = $_SERVER['sinfo']['chat_window_options'];
			if($opts=='') $opts = array();
			else $opts = unserialize($opts);
			$opts['departments'] = $departments;
			$nopts = serialize($opts);
			$nopts_esc = mysql_real_escape_string($nopts,$dbh);
			$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
			$SQIN = "UPDATE $tbl SET chat_window_options='$nopts_esc' WHERE siteid='$server_siteid' ";
			mysql_query($SQIN,$dbh);
			mysql_close($dbh);
			$RV['status'] = 1;
			$RV['action'] = 'document.conf_dialogs["dialog_departments"].lv.DepartmentsReload()';
		}
		return($RV);
	}
	function UpdateFieldsAddPref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		$RV['status'] = 0;
		$r['name'] = strtolower($r['name']);
		$flist = $_SERVER['sinfo']['extra_fields'];
		if($flist=='') $flist = array();
		else $flist = unserialize($flist);
		if($_SERVER['sinfo']['in_trial']==1 && count($flist)>=1)
		{
			$RV['error'] = '<s5lang>Cannot add more than 1 field when account is in trial mode</s5lang>';
		}
		elseif(strlen($r['name'])<2)
		{
			$RV['error'] = '<s5lang>Field name should be at least 2 characters</s5lang>';
		}
		elseif(strlen($r['label'])<2)
		{
			$RV['error'] = '<s5lang>Field label should be at least 2 characters</s5lang>';
		}
		elseif(InputFilter($r['name'],LIVEADMIN_CON_D.LIVEADMIN_CON_L.'_')!=$r['name'])
		{
			$RV['error'] = '<s5lang>Field name should only contain letters, digits and under score.</s5lang>';
		}
		else
		{
			$f_exists = false;
			foreach($flist as $a=>$v)
			{
				if($v['name']==$r['name'])
				{
					$RV['error'] = '<s5lang>Field name exists, please delete it first or use a different name.</s5lang>';
					$f_exists = true;
					break;
				}
			}
			if($f_exists==false)
			{
				$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
				mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
				$tbl = LIVEADMIN_DB_PREFIX.'sites';
				$inf = array ( 'name' => $r['name'], 'type' => $r['type'], 'label' => $r['label'], 'default' => $r['default'] );
				if($r['type']=='select')
				{
					$inf['options'] = $r['options'];
				}
				$flist[] = $inf;
				$nflist = serialize($flist);
				$nflist_esc = mysql_real_escape_string($nflist,$dbh);
				$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
				$SQIN = "UPDATE $tbl SET extra_fields='$nflist_esc' WHERE siteid='$server_siteid' ";
				mysql_query($SQIN,$dbh);
				mysql_close($dbh);
				$RV['status'] = 1;
				$RV['action'] = 'document.conf_dialogs["dialog_fields"].lv.FieldsReload()';
			}
		}
		return($RV);
	}
	function UpdateFieldsEditPref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		$RV['status'] = 0;
		$r['name'] = strtolower($r['name']);
		$r['old_name'] = strtolower($r['old_name']);
		$flist = $_SERVER['sinfo']['extra_fields'];
		if($flist=='') $flist = array();
		else $flist = unserialize($flist);
		if(strlen($r['name'])<2)
		{
			$RV['error'] = '<s5lang>Field name should be at least 2 characters</s5lang>';
		}
		elseif(strlen($r['label'])<2)
		{
			$RV['error'] = '<s5lang>Field label should be at least 2 characters</s5lang>';
		}
		elseif(InputFilter($r['name'],LIVEADMIN_CON_D.LIVEADMIN_CON_L.'_')!=$r['name'])
		{
			$RV['error'] = '<s5lang>Field name should only contain letters, digits and under score.</s5lang>';
		}
		else
		{
			$f_exists = false;
			$f_index = -1;
			foreach($flist as $a=>$v)
			{
				if($v['name']==$r['old_name'])
				{
					$f_exists = true;
					$f_index = $a;
					break;
				}
			}
			if($f_exists==false)
			{
				$RV['error'] = '<s5lang>Field does not exist, please try to exit the fields dialog and try again.</s5lang>';
			}
			else
			{
				$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
				mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
				$tbl = LIVEADMIN_DB_PREFIX.'sites';
				$inf = array ( 'name' => $r['name'], 'type' => $r['type'], 'label' => $r['label'], 'default' => $r['default'] );
				if($r['type']=='select')
				{
					$inf['options'] = $r['options'];
				}
				$flist[$f_index] = $inf;
				$nflist = serialize($flist);
				$nflist_esc = mysql_real_escape_string($nflist,$dbh);
				$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
				$SQIN = "UPDATE $tbl SET extra_fields='$nflist_esc' WHERE siteid='$server_siteid' ";
				mysql_query($SQIN,$dbh);
				mysql_close($dbh);
				$RV['status'] = 1;
				$RV['action'] = 'document.conf_dialogs["dialog_fields"].lv.FieldsReload()';
			}
		}
		return($RV);
	}
	function UpdateFieldsDeletePref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		$RV['status'] = 0;
		$r['name'] = strtolower($r['name']);
		$fieldid = $r['fieldid'];
		$flist = $_SERVER['sinfo']['extra_fields'];
		if($flist=='') $flist = array();
		else $flist = unserialize($flist);
		$f_exists = false;
		$f_index = -1;
		foreach($flist as $a=>$v)
		{
			if(md5($v['name'])==$fieldid)
			{
				$f_exists = true;
				$f_index = $a;
				break;
			}
		}
		if($f_exists==false)
		{
			$RV['error'] = '<s5lang>Field does not exist, please try to exit the fields dialog and try again.</s5lang>';
		}
		else
		{
			$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
			mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
			$tbl = LIVEADMIN_DB_PREFIX.'sites';
			unset($flist[$f_index]);
			$aflist = array();
			foreach($flist as $a=>$v)
			{
				$aflist[] = $v;
			}
			$nflist = serialize($aflist);
			$nflist_esc = mysql_real_escape_string($nflist,$dbh);
			$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
			$SQIN = "UPDATE $tbl SET extra_fields='$nflist_esc' WHERE siteid='$server_siteid' ";
			mysql_query($SQIN,$dbh);
			mysql_close($dbh);
			$RV['status'] = 1;
			$RV['action'] = 'document.conf_dialogs["dialog_fields"].lv.FieldsReload()';
		}
		return($RV);
	}
	function UpdateNewsAddPref($r)
	{
		$RV = array();
		$RV['status'] = 0;
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		list($dt_m,$dt_d,$dt_y) = explode('/',$r['date']);
		$r['date'] = lv_mktime(0,0,0,$dt_m,$dt_d,$dt_y,$_SERVER['uinfo']['time_zone']);
		if($r['link']=='http://') $r['link'] = '';
		$inps = array ( 'date', 'title', 'link', 'text', 'active', 'sticky' );
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'news';
		$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$SQL_AR = array();
		foreach($inps as $a=>$v)
		{
			$SQL_AR[] = 'n_'.$v.'="'.mysql_real_escape_string(trim($r[$v]),$dbh).'"';
		}
		$SQIN = "INSERT INTO $tbl SET siteid='$server_siteid', ".implode(' , ',$SQL_AR)." ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV['status'] = 1;
		$RV['action'] = 'document.conf_dialogs["dialog_news"].lv.NewsReload();
ReloadNews();';
		return($RV);
	}
	function UpdateNewsEditPref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		$RV['status'] = 0;
		$newsid = $r['newsid'];
		if($r['mode']=='single')
		{
			$n_field = trim($r['news_field']);
			$n_value = trim($r['news_value']);
			switch($n_field)
			{
				case 'active': $n_field = 'n_active';
				$n_value = ($n_value=='y')?1:0;
				break;
				case 'sticky': $n_field = 'n_sticky';
				$n_value = ($n_value=='y')?1:0;
				break;
				default: $RV['status'] = 'Not implemented';
				return($RV);
			}
			if($this->GetSiteNewsByID($newsid)===false)
			{
				$RV['status'] = 'Invalid news feed';
				return($RV);
			}
			$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
			mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
			$tbl = LIVEADMIN_DB_PREFIX.'news';
			$n_field_esc = mysql_real_escape_string($n_field,$dbh);
			$n_value_esc = mysql_real_escape_string($n_value,$dbh);
			$newsid_esc = mysql_real_escape_string($newsid,$dbh);
			$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
			$SQIN = "UPDATE $tbl SET $n_field_esc='$n_value_esc' WHERE siteid='$server_siteid' AND newsid='$newsid_esc' ";
			mysql_query($SQIN,$dbh);
			mysql_close($dbh);
			$RV['status'] = 1;
			$RV['action'] = 'ReloadNews();';
		}
		if($r['mode']=='multi')
		{
			list($dt_m,$dt_d,$dt_y) = explode('/',$r['date']);
			$r['date'] = lv_mktime(0,0,0,$dt_m,$dt_d,$dt_y,$_SERVER['uinfo']['time_zone']);
			if($r['link']=='http://') $r['link'] = '';
			$inps = array ( 'date', 'title', 'link', 'text', 'active', 'sticky' );
			$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
			mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
			$tbl = LIVEADMIN_DB_PREFIX.'news';
			$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
			$newsid_esc = mysql_real_escape_string($newsid,$dbh);
			$SQL_AR = array();
			foreach($inps as $a=>$v)
			{
				$SQL_AR[] = 'n_'.$v.'="'.mysql_real_escape_string(trim($r[$v]),$dbh).'"';
			}
			$SQIN = "UPDATE $tbl SET ".implode(' , ',$SQL_AR)." WHERE siteid='$server_siteid' AND newsid='$newsid_esc' ";
			mysql_query($SQIN,$dbh);
			mysql_close($dbh);
			$RV['status'] = 1;
			$RV['action'] = 'document.conf_dialogs["dialog_news"].lv.NewsReload();
ReloadNews();';
		}
		return($RV);
	}
	function UpdateNewsDeletePref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV = array();
		$RV['status'] = 0;
		$newsid = $r['newsid'];
		if($this->GetSiteNewsByID($newsid)===false)
		{
			$RV['status'] = 'Invalid news feed';
			return($RV);
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'news';
		$server_siteid = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$newsid_esc = mysql_real_escape_string($newsid,$dbh);
		$SQIN = "DELETE FROM $tbl WHERE siteid='$server_siteid' AND newsid='$newsid_esc' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV['status'] = 1;
		$RV['action'] = 'document.conf_dialogs["dialog_news"].lv.NewsReload();
ReloadNews();';
		return($RV);
	}
	function UpdatePhotoPref($r,$f)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$RV['status'] = 0;
		$rand = RandomString(32);
		if(!is_uploaded_file($_FILES['new_photo']['tmp_name']))
		{
			$RV['error'] = '<s5lang>No photo specified for upload.</s5lang>';
		}
		else
		{
			$file = $_FILES['new_photo']['tmp_name'];
			if(!ImageResize($file,LIVEADMIN_TEMP_FC.'/'.$rand.'_large',320,200,true))
			{
				$RV['error'] = '<s5lang>Invalid or unrecognized image format</s5lang>';
			}
			elseif(!ImageResize($file,LIVEADMIN_TEMP_FC.'/'.$rand.'_small',32,20,true))
			{
				$RV['error'] = '</s5lang>Invalid or unrecognized image format</s5lang>';
			}
			else
			{
				$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
				mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
				$tbl = LIVEADMIN_DB_PREFIX.'users';
				$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
				$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
				$SQIN = "UPDATE $tbl SET has_pic=1 WHERE userid='$userid_esc' AND siteid='$siteid_esc' ";
				mysql_query($SQIN,$dbh);
				mysql_close($dbh);
				$photo_large = encode64(file_get_contents(LIVEADMIN_TEMP_FC.'/'.$rand.'_large'));
				$photo_small = encode64(file_get_contents(LIVEADMIN_TEMP_FC.'/'.$rand.'_small'));
				$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
				mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
				$tbl = LIVEADMIN_DB_PREFIX.'photo';
				$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
				$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
				mysql_query("DELETE FROM $tbl WHERE userid='$userid_esc' AND siteid='$siteid_esc' ",$dbh);
				mysql_query("INSERT INTO $tbl SET userid='$userid_esc', siteid='$siteid_esc' ",$dbh);
				mysql_query("UPDATE $tbl SET photo_large='$photo_large' WHERE userid='$userid_esc' AND siteid='$siteid_esc' ",$dbh);
				mysql_query("UPDATE $tbl SET photo_small='$photo_small' WHERE userid='$userid_esc' AND siteid='$siteid_esc' ",$dbh);
				mysql_close($dbh);
				$RV['status'] = 1;
				$RV['action'] = 'document.conf_dialogs["dialog_photo"].lv.PhotoReload()';
			}
		}
		@unlink(LIVEADMIN_TEMP_FC.'/'.$rand.'_large');
		@unlink(LIVEADMIN_TEMP_FC.'/'.$rand.'_small');
		return($RV);
	}
	function UpdatePhotoDeletePref($r)
	{
		if($_SERVER['sinfo']['demo_mode']==1)
		{
			return(array('status'=>2,'info'=>'<s5lang>Can not edit or update preferences in demo mode.</s5lang>'));
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'users';
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$SQIN = "UPDATE $tbl SET has_pic=0 WHERE userid='$userid_esc' AND siteid='$siteid_esc' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'photo';
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$SQIN = "DELETE FROM $tbl WHERE userid='$userid_esc' AND siteid='$siteid_esc' ";
		mysql_query($SQIN,$dbh);
		$RV['status'] = 1;
		$RV['action'] = 'document.conf_dialogs["dialog_photo"].lv.PhotoReload()';
	}
	function LiveAdminConf()
	{
		$RV = array();
		$RV['uinfo_nickname'] = $_SERVER['uinfo']['nickname'];
		$RV['uinfo_language'] = $_SERVER['uinfo']['language'];
		$RV['sinfo_company'] = $_SERVER['sinfo']['company'];
		$RV['uinfo_sound_new_client'] = $_SERVER['uinfo']['sound_new_client'];
		$RV['uinfo_sound_new_message'] = $_SERVER['uinfo']['sound_new_message'];
		$RV['can_see_agents'] = ($_SERVER['uinfo']['can_see_agents']==0)?'no':'yes';
		if($_SERVER['uinfo']['access_level']==0) $RV['can_see_agents'] = 'yes';
		$RV['can_see_visitors'] = ($_SERVER['uinfo']['can_see_visitors']==0)?'no':'yes';
		if($_SERVER['uinfo']['access_level']==0) $RV['can_see_visitors'] = 'yes';
		$RV['hide_news'] = ($_SERVER['uinfo']['hide_news']=='n')?'no':'yes';
		if($_SERVER['uinfo']['access_level']==0) $RV['hide_news'] = 'no';
		if($_SERVER['sinfo']['enable_callback']>=1) $RV['enable_callback'] = 'yes';
		else $RV['enable_callback'] = 'no';
		$RV['lite'] = 'n';
		if(LIVEADMIN_LITE)
		{
			$RV['lite'] = 'y';
			$RV['enable_callback'] = 'no';
		}
		if($_SERVER['uinfo']['access_level']==0) $RV['access_level'] = 'root';
		else $RV['access_level'] = 'agent';
		if(LIVEADMIN_STANDALONE)
		{
			$RV['standalone'] = 'y';
			$RV['check_update'] = ($_SERVER['uinfo']['check_update']==0)?'n':'y';
		}
		else
		{
			$RV['standalone'] = 'n';
			$RV['check_update'] = 'n';
		}
		$RV['logout_url'] = LIVEADMIN_WC.'?act=logout';
		$RV['userid'] = $_SERVER['uinfo']['userid'];
		$RV['siteid'] = $_SERVER['uinfo']['siteid'];
		$RV['online_status'] = 'yes';
		if($_SERVER['sinfo']['expiry_date']<time()) $RV['online_status'] = 'no';
		if($_SERVER['sinfo']['demo_mode']==1) $RV['demo_mode'] = 'yes';
		else $RV['demo_mode'] = 'no';
		if($_SERVER['sinfo']['in_trial']==1) $RV['trial_mode'] = 'yes';
		else $RV['trial_mode'] = 'no';
		if(defined('RENEW_YEAR_1'))
		{
			$renew_net = RENEW_YEAR_1;
		}
		else
		{
			$renew_net = 49;
		}
		$renew_gst = 0;
		$renew_pst = 0;
		if($_SERVER['sinfo']['country']=='CA')
		{
			$renew_gst = $renew_net*GST_RATE;
			if($_SERVER['sinfo']['state']=='BC') $renew_pst = $renew_net*PST_RATE;
		}
		$renew_tot = $renew_net+$renew_gst+$renew_pst;
		$RV['renew_net'] = 's'.number_format($renew_net,2,'.','');
		$RV['renew_pst'] = 's'.number_format($renew_pst,2,'.','');
		$RV['renew_gst'] = 's'.number_format($renew_gst,2,'.','');
		$RV['renew_tot'] = 's'.number_format($renew_tot,2,'.','');
		if(isset($_SERVER['lv_sid'])) $lv_sid = $_SERVER['lv_sid'];
		else $lv_sid = "";
		$RV['lv_sid'] = $lv_sid;
		$RV['sound_flash_source'] = LIVEADMIN_WSND;
		$RV['sound_files_path'] = LIVEADMIN_WR;
		$RV['lv_version'] = LIVEADMIN_VERSION;
		$lang = LoadLang($_SERVER['uinfo']['language']);
		$RV['TL'] = array();
		foreach($lang->GetAllStrings() as $a=>$v)
		{
			if(substr($a,0,1)=='B') $RV['TL'][$a] = $v;
		}
		$start_date = time()-(30*24*3600);
		$end_date = time();
		$RV['log_start_date_d'] = lv_date('d',$start_date,$_SERVER['uinfo']['time_zone']);
		$RV['log_start_date_m'] = lv_date('n',$start_date,$_SERVER['uinfo']['time_zone']);
		$RV['log_start_date_y'] = lv_date('Y',$start_date,$_SERVER['uinfo']['time_zone']);
		$RV['log_start_date_m3'] = lv_date('M',$start_date,$_SERVER['uinfo']['time_zone']);
		$RV['log_end_date_d'] = lv_date('d',$end_date,$_SERVER['uinfo']['time_zone']);
		$RV['log_end_date_m'] = lv_date('n',$end_date,$_SERVER['uinfo']['time_zone']);
		$RV['log_end_date_y'] = lv_date('Y',$end_date,$_SERVER['uinfo']['time_zone']);
		$RV['log_end_date_m3'] = lv_date('M',$end_date,$_SERVER['uinfo']['time_zone']);
		foreach($_SERVER['CONTROL_MESSAGES'] as $a=>$v)
		{
			$RV['CTL_'.$a] = $v;
		}
		return($RV);
	}
	function GetSounds()
	{
		$RV = array();
		$RV['sound_file_1'] = ($_SERVER['uinfo']['sound_new_client']=='--none--')?'':LIVEADMIN_WR.'/'.$_SERVER['uinfo']['sound_new_client'];
		$RV['sound_file_2'] = ($_SERVER['uinfo']['sound_new_message']=='--none--')?'':LIVEADMIN_WR.'/'.$_SERVER['uinfo']['sound_new_message'];
		$RV['status'] = 1;
		sleep(1);
		return($RV);
	}
	function GetAllSounds()
	{
		include_once('dialog.php');
		$lv_dialog = new LV_Dialog($this);
		$rings = $lv_dialog->GetRings();
		unset($lv_dialog);
		$RV = array();
		foreach($rings as $a=>$v)
		{
			if($a=='--none--') continue;
			if(is_file(LIVEADMIN_R.'/'.$a))
			{
				$RV[] = array('file'=>$a,'name'=>$v,'size'=>filesize(LIVEADMIN_R.'/'.$a));
			}
		}
		return($RV);
	}
	function GetLicInfo()
	{
		$RV = array();
		$license = $_SERVER['sinfo']['license'];
		$force_get = false;
		if(isset($_REQUEST['mode']) && $_REQUEST['mode']=='register')
		{
			$license = $_REQUEST['lic'];
			$force_get = true;
			$lic_check_mode = 'register';
			$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
			mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
			$tbl = LIVEADMIN_DB_PREFIX.'sites';
			$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
			$license_esc = mysql_real_escape_string(InputFilter(strtoupper($license),LIVEADMIN_CON_U),$dbh);
			$SQIN = "UPDATE $tbl SET license='$license_esc' WHERE siteid='$siteid_esc' ";
			mysql_query($SQIN,$dbh);
			mysql_close($dbh);
		}
		if($force_get || $_SERVER['sinfo']['license_expiry_date']<time())
		{
			$lic_check_mode = 'check';
			$RV['status'] = 1;
			$RV['lic'] = $license;
			$RV['suwy'] = mt_rand(5,15);
			$RV['suwn'] = mt_rand(16,25);
			$lic = encode64('2|'.LIVEADMIN_VERSION.'|'.InputFilter(strtoupper($license),LIVEADMIN_CON_U).'|'.$RV['suwy'].'|'.$RV['suwn'].'|'.$_SERVER['sinfo']['siteid'].'|'.$_SERVER['SERVER_ADDR'].'|'.$_SERVER['SERVER_NAME'].'|'.$lic_check_mode.'|');
			$RV['toget'] = 'https://licserv.liveadmin.net/chklic.php?lic='.$lic.'&rnd='.mt_rand(10000,99999);
		}
		else
		{
			$RV['status'] = 0;
		}
		return($RV);
	}
	function SetLicInfo()
	{
		$status = $_REQUEST['lic_st'];
		$source = $_REQUEST['lic_src'];
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'sites';
		switch($source)
		{
			case 'reg': if($status==1)
			{
				$dlt = time()+(48*3600);
				$lic_sq = ",in_trial=0, refid=0";
			}
			else
			{
				$dlt = time()+(2*3600);
				$lic_sq = ",in_trial=1, license='', refid=0";
			}
			break;
			default: case 'check': if($status==1)
			{
				$dlt = time()+(48*3600);
				$lic_sq = ",in_trial=0, refid=0";
			}
			else
			{
				if($_SERVER['sinfo']['license_retry_check_count']>10)
				{
					$dlt = time()+(2*3600);
					$lic_sq = ",in_trial=1, license=''";
				}
				else
				{
					$dlt = time()+(2*3600);
					$nrefid = $_SERVER['sinfo']['license_retry_check_count']+1;
					$lic_sq = ", refid=".$nrefid;
				}
			}
			break;
		}
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$SQIN = "UPDATE $tbl SET expiry_date=$dlt $lic_sq WHERE siteid='$siteid_esc' ";
		mysql_query($SQIN,$dbh);
		mysql_close($dbh);
		$RV = array();
		$RV['status'] = 1;
		return($RV);
	}
	function GetAgents()
	{
		$ulist = GetUsersList($_SERVER['sinfo']['siteid']);
		$RV = array();
		$RV['list'] = array();
		foreach($ulist as $a=>$v)
		{
			switch($v['access_level'])
			{
				case 0: $access_level_text = 'Root';
				break;
				case 1: $access_level_text = 'Agent';
				break;
			}
			switch($v['ac_status'])
			{
				case 0: $ac_status_text = 'Suspended';
				break;
				case 1: $ac_status_text = 'Active';
				break;
			}
			if($_SERVER['uinfo']['userid']==$v['userid'])
			{
				$can_delete = 0;
				$can_edit = 0;
			}
			else
			{
				$can_delete = 1;
				$can_edit = 1;
			}
			$RV['list'][] = array ( 'userid'=>$v['userid'], 'username'=>$v['username'], 'nickname'=>$v['nickname'], 'access_level'=>$v['access_level'], 'access_level_text'=>$access_level_text, 'ac_status'=>$v['ac_status'], 'ac_status_text'=>$ac_status_text, 'can_delete'=>$can_delete, 'can_edit'=>$can_edit );
		}
		$RV['status'] = 1;
		return($RV);
	}
	function GetAgentsDept($dialog_id)
	{
		$RV = array();
		$ulist = GetUsersList($_SERVER['sinfo']['siteid']);
		if($dialog_id=='dialog_departments_add')
		{
			foreach($ulist as $a=>$v)
			{
				$access_level_text = '';
				switch($v['access_level'])
				{
					case 0: $access_level_text = 'Root';
					break;
					case 1: $access_level_text = 'Agent';
					break;
				}
				$RV[] = array ( 'userid' => $v['userid'], 'username'=>$v['username'], 'nickname'=>$v['nickname'], 'access_level'=>$v['access_level'], 'access_level_text'=>$access_level_text, 'dept'=>'n' );
			}
		}
		else if($dialog_id=='dialog_departments_edit')
		{
			$departments = $this->GetDepartmentsList();
			foreach($ulist as $a=>$v)
			{
				$dept = 'n';
				if(isset($departments[$_REQUEST['depid']]['agents']) && isset($departments[$_REQUEST['depid']]['agents'][$v['userid']]) )
				{
					$dept = 'y';
				}
				$access_level_text = '';
				switch($v['access_level'])
				{
					case 0: $access_level_text = 'Root';
					break;
					case 1: $access_level_text = 'Agent';
					break;
				}
				$RV[] = array ( 'userid' => $v['userid'], 'username'=>$v['username'], 'nickname'=>$v['nickname'], 'access_level'=>$v['access_level'], 'access_level_text'=>$access_level_text, 'dept'=>$dept );
			}
		}
		return($RV);
	}
	function GetFields()
	{
		$flist = $_SERVER['sinfo']['extra_fields'];
		if($flist=='') $flist = array();
		else $flist = unserialize($flist);
		$RV = array();
		$RV['list'] = array();
		foreach($flist as $a=>$v)
		{
			$v['fieldid'] = md5($v['name']);
			$RV['list'][] = $v;
		}
		$RV['status'] = 1;
		return($RV);
	}
	function GetSiteNews()
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'news';
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$RV = array();
		$RV['status'] = 1;
		$RV['list'] = array();
		$res = mysql_query("SELECT * FROM $tbl WHERE siteid='$siteid_esc' ORDER BY n_date DESC",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV['list'][] = array ( 'newsid' => $req['newsid'], 'date' => $req['n_date'], 'date_text' => lv_date("j M Y",$req['n_date'],$_SERVER['uinfo']['time_zone']), 'title' => $req['n_title'], 'active' => (($req['n_active']==1)?'y':'n'), 'sticky' => (($req['n_sticky']==1)?'y':'n') );
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function GetSiteNewsByID($newsid)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'news';
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$newsid_esc = mysql_real_escape_string($newsid,$dbh);
		$RV = false;
		$res = mysql_query("SELECT * FROM $tbl WHERE siteid='$siteid_esc' AND newsid='$newsid_esc'",$dbh);
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
	function GetDepartmentsList()
	{
		$departments = array();
		if($_SERVER['sinfo']['chat_window_options']!='')
		{
			$chat_window_options = unserialize($_SERVER['sinfo']['chat_window_options']);
			if(isset($chat_window_options['departments']) && is_array($chat_window_options['departments'])) $departments = $chat_window_options['departments'];
		}
		return($departments);
	}
	function GetDepartments()
	{
		$departments = $this->GetDepartmentsList();
		$RV = array();
		foreach($departments as $a=>$v)
		{
			$RV['list'][] = array ( 'depid'=> $a, 'name' => $v['name'], 'agents_count' => count($v['agents']), 'can_delete' => (count($v['agents'])>0?'n':'y') );
		}
		$RV['status'] = 1;
		return($RV);
	}
	function GetAgentPhoto($size)
	{
		if($_SERVER['uinfo']['has_pic']==0 || ($size!='small' && $size!='large'))
		{
			header('Content-Type: image/png');
			print file_get_contents(LIVEADMIN_FI.'/no_photo.png');
		}
		else
		{
			$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
			mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
			$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
			$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
			$photo_enc = '';
			$tbl = LIVEADMIN_DB_PREFIX.'photo';
			$res = mysql_query("SELECT photo_$size as photo_enc FROM $tbl WHERE userid='$userid_esc' AND siteid='$siteid_esc' ",$dbh);
			if($res)
			{
				while($req = mysql_fetch_array($res))
				{
					$photo_enc = $req['photo_enc'];
					break;
				}
			}
			mysql_close($dbh);
			header('Content-Type: image/png');
			if($photo_enc=='')
			{
				print file_get_contents(LIVEADMIN_FI.'/no_photo.png');
			}
			else
			{
				$photo = decode64($photo_enc);
				print $photo;
			}
		}
	}
	function GetBlockedClients()
	{
		$RV = array();
		$RV['list'] = array();
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl_banned = LIVEADMIN_DB_PREFIX.'banned';
		$tbl_users = LIVEADMIN_DB_PREFIX.'users';
		$dtm = time();
		mysql_query("DELETE FROM $tbl_banned WHERE expiry_date<=$dtm ",$dbh);
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$res = mysql_query("SELECT banned_tbl.*, users_tbl.nickname FROM $tbl_banned AS banned_tbl Left Join $tbl_users AS users_tbl ON users_tbl.userid = banned_tbl.userid AND users_tbl.siteid = banned_tbl.siteid WHERE banned_tbl.siteid='$siteid_esc' AND banned_tbl.expiry_date>$dtm", $dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV['list'][] = array ( 'bid'=>$req['bid'], 'active'=>$req['active'], 'active_text'=>($req['active']==1)?'<s5lang>Yes</s5lang>':'<s5lang>No</s5lang>', 'client_ip'=>$req['client_ip'], 'validity'=>TimeDiffText($req['expiry_date']-$dtm,true), 'nickname'=>$req['nickname'] );
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function ModifyBlockedClients()
	{
		$RV = array('status'=>0);
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'banned';
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		if(isset($_REQUEST['mode']) && isset($_REQUEST['bid']))
		{
			switch($_REQUEST['mode'])
			{
				case 'set_active': if(isset($_REQUEST['active']) && ($_REQUEST['active']==0 || $_REQUEST['active']==1))
				{
					$bid_esc = mysql_real_escape_string($_REQUEST['bid'],$dbh);
					$active_esc = mysql_real_escape_string($_REQUEST['active'],$dbh);
					mysql_query("UPDATE $tbl SET active='$active_esc' WHERE bid=$bid_esc AND siteid=$siteid_esc ",$dbh);
					$RV['status']=1;
				}
				break;
				case 'set_period': if(isset($_REQUEST['period_hours']))
				{
					$bid_esc = mysql_real_escape_string($_REQUEST['bid'],$dbh);
					$dtm = time()+($_REQUEST['period_hours']*3600);
					mysql_query("UPDATE $tbl SET expiry_date='$dtm' WHERE bid=$bid_esc AND siteid=$siteid_esc ",$dbh);
					$RV['status']=1;
					$RV['action'] = 'document.conf_dialogs["dialog_blocked_clients"].lv.BlockedClientsReload()';
				}
				break;
				case 'delete': $bid_esc = mysql_real_escape_string($_REQUEST['bid'],$dbh);
				mysql_query("DELETE FROM $tbl WHERE bid=$bid_esc AND siteid=$siteid_esc ",$dbh);
				$RV['status']=1;
				break;
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function GetNews()
	{
		include_once('cache.php');
		$RV = array();
		$RV['status'] = 1;
		$RV['data'] = array();
		$cache = new LV_Cache();
		$cached_feed = $cache->GetCache('NEWS_FEED',2*3600);
		$RVT = array();
		$LiveNews = array();
		$reload_data = true;
		if($cached_feed!==false)
		{
			$LiveNews = unserialize($cached_feed);
			$reload_data = false;
		}
		if(!is_array($LiveNews)) $reload_data = true;
		if($reload_data)
		{
			if(LIVEADMIN_STANDALONE) $source = 'standalone';
			else $source = 'hosted';
			$header[] = "Accept: text/vnd.wap.wml,*.*";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://www.liveadmin.net/admin/news.php?format=serialize&source=".$source);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			@curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch, CURLOPT_VERBOSE,0);
			curl_setopt($ch, CURLOPT_USERAGENT,lv_get_curl_useragent());
			curl_setopt($ch, CURLOPT_POST,0);
			curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,30);
			curl_setopt($ch, CURLOPT_TIMEOUT,180);
			$data = curl_exec ($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			$LiveNews = unserialize($data);
			if(is_array($LiveNews)) $cache->SetCache('NEWS_FEED',$data);
		}
		foreach($LiveNews as $a=>$v)
		{
			$RVT[] = array ( 'type' => 'lv', 'date_text' => lv_date("D, j M Y H:i:s",$v['date'],$_SERVER['uinfo']['time_zone']), 'date' => $v['date'], 'link' => $v['link'], 'title' => $v['title'], 'text' => $v['text'], 'sticky' => 'n' );
		}
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'news';
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$res = mysql_query("SELECT * FROM $tbl WHERE siteid=$siteid_esc AND n_active=1 ORDER BY n_date DESC ",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RVT[] = array ( 'type' =>'st', 'date_text' =>lv_date("D, j M Y",$req['n_date'],$_SERVER['uinfo']['time_zone']), 'date' =>$req['n_date'], 'link' =>strval($req['n_link']), 'title' =>strval($req['n_title']), 'text' =>nl2br(strval($req['n_text'])), 'sticky' =>(($req['n_sticky']==1)?'y':'n') );
			}
		}
		mysql_close($dbh);
		usort($RVT,"lv_sort_news_feeds_callback");
		$RV['data'] = $RVT;
		return($RV);
	}
	function ArchiveMissedCall($client_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$server_uniq = RandomString(32);
		$server_uniq_esc = mysql_real_escape_string($server_uniq,$dbh);
		$server_nickname_esc = mysql_real_escape_string($_SERVER['uinfo']['nickname'],$dbh);
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$server_userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		mysql_query("UPDATE $tbl SET server_uniq='$server_uniq_esc', server_dtm=0, server_userid='$server_userid_esc', server_nickname='$server_nickname_esc' WHERE client_uniq='$client_uniq_esc' AND server_uniq is NULL ",$dbh);
		mysql_close($dbh);
		$this->ArchiveChat($client_uniq,$server_uniq);
	}
	function DeleteMissedCall($client_uniq)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		mysql_query("DELETE FROM $tbl WHERE client_uniq='$client_uniq_esc' AND server_uniq is NULL ",$dbh);
		mysql_close($dbh);
	}
	function CleanupVisitors()
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'visitors';
		$dtm = time()-(10*60);
		mysql_query("DELETE FROM $tbl WHERE dtm<$dtm ",$dbh);
		mysql_close($dbh);
	}
	function StartVisitorChat($ip)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'visitors';
		$departments = lv_get_departments($_SERVER['sinfo']);
		$depid = -1;
		if(count($departments)>0)
		{
			foreach($departments as $a=>$v)
			{
				if(isset($v['agents'][$_SERVER['uinfo']['userid']]))
				{
					$depid = $a;
					break;
				}
			}
		}
		$ip_esc = mysql_real_escape_string($ip,$dbh);
		$siteid_esc = mysql_real_escape_string($_SERVER['sinfo']['siteid'],$dbh);
		$caller_sid_esc = mysql_real_escape_string($_REQUEST['lv_sid'].':'.$depid,$dbh);
		mysql_query("UPDATE $tbl SET caller_sid='$caller_sid_esc' WHERE siteid=$siteid_esc AND ip='$ip_esc' ",$dbh);
		mysql_close($dbh);
		if(LIVEADMIN_STANDALONE)
		{
			$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
			mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
			$tbl = LIVEADMIN_DB_PREFIX.'visitors_trig';
			$dtm = RealTime();
			$ip_esc = mysql_real_escape_string($ip,$dbh);
			mysql_query("INSERT INTO $tbl SET dtm=$dtm, ip='$ip_esc' ",$dbh);
			mysql_close($dbh);
		}
		else
		{
			include_once("s3aws.php");
			$lvs3 = new LV_S3AWS();
			$lvs3->TriggerChat($ip);
			unset($lvs3);
		}
	}
	function AddDraft($message)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'draft';
		$message = CheckEnc($message);
		if(!lv_is_utf8($message)) $message = lv_convert_utf8($message);
		$message = NoMultiLine($message);
		$message = StripHTML($message);
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$msg_tag = mysql_real_escape_string(substr($message,0,20),$dbh);
		$message_esc = mysql_real_escape_string($message,$dbh);
		$message_md5 = mysql_real_escape_string(md5($message),$dbh);
		$edate = time();
		mysql_query("INSERT INTO $tbl SET userid='$userid_esc', message_md5='$message_md5', message_tag='$msg_tag', message='$message_esc', edate='$edate' ",$dbh);
		mysql_close($dbh);
	}
	function DeleteDraft($msg_md5)
	{
		$RV = array();
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'draft';
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$msg_md5_esc = mysql_real_escape_string($msg_md5,$dbh);
		mysql_query("DELETE FROM $tbl WHERE userid=$userid_esc AND message_md5='$msg_md5_esc' ",$dbh);
		mysql_close($dbh);
		$RV['status'] = 1;
		$RV['action'] = 'document.conf_dialogs["dialog_drafts"].lv.DraftReload()';
		return($RV);
	}
	function GetDrafts()
	{
		$RV = array();
		$RV['list'] = array();
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'draft';
		$userid_esc = mysql_real_escape_string($_SERVER['uinfo']['userid'],$dbh);
		$res = mysql_query("SELECT * FROM $tbl WHERE userid=$userid_esc ORDER BY message_tag ",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV['list'][] = array ( 'msg_md5'=>$req['message_md5'], 'edate'=>$req['edate'], 'message'=>$req['message'] );
			}
		}
		mysql_close($dbh);
		$RV['status'] = 1;
		return($RV);
	}
	function GetBusyUsers()
	{
		$RV = array();
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$sitekey_esc = mysql_real_escape_string(ID2Key($_SERVER['sinfo']['siteid']),$dbh);
		$res = mysql_query("SELECT server_userid FROM $tbl WHERE site_key='$sitekey_esc' AND (client_flag=4 OR client_flag=5 OR client_flag=6 client_flag=7) ",$dbh);
		if($res)
		{
			while($req = mysql_fetch_array($res))
			{
				$RV[$req['server_userid']] = true;
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function GetWaitInfoByClientUniq($client_uniq)
	{
		$RV = false;
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$sitekey_esc = mysql_real_escape_string(ID2Key($_SERVER['sinfo']['siteid']),$dbh);
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$res = mysql_query("SELECT * FROM $tbl WHERE client_uniq='$client_uniq_esc' AND site_key='$sitekey_esc' ",$dbh);
		if($res)
		{
			while($req = mysql_fetch_assoc($res))
			{
				$RV = $req;
				break;
			}
		}
		mysql_close($dbh);
		return($RV);
	}
	function UpdateWaitListForTransfer($winfo,$client_uniq,$temp_client_uniq,$client_info,$server_uniq_old,$server_uniq_new)
	{
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'wait';
		$sitekey_esc = mysql_real_escape_string(ID2Key($_SERVER['sinfo']['siteid']),$dbh);
		$client_uniq_esc = mysql_real_escape_string($client_uniq,$dbh);
		$client_info_esc = mysql_real_escape_string($client_info,$dbh);
		$temp_client_uniq = mysql_real_escape_string($temp_client_uniq,$dbh);
		$SQL = "INSERT INTO $tbl SET client_uniq='$temp_client_uniq' ";
		foreach($winfo as $a=>$v)
		{
			if($a=='client_uniq') continue;
			$SQL .= ','.$a.'="'.mysql_real_escape_string($v,$dbh).'"';
		}
		mysql_query($SQL,$dbh);
		mysql_query("UPDATE $tbl SET client_info='$client_info_esc', client_flag=1, server_uniq='', server_dtm=0, server_userid=0, server_nickname='' WHERE client_uniq='$client_uniq_esc' AND site_key='$sitekey_esc' ",$dbh);
		$server_uniq_new_esc = mysql_real_escape_string($server_uniq_new,$dbh);
		$server_uniq_old_esc = mysql_real_escape_string($server_uniq_old,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'msg';
		$res = mysql_query("SELECT * FROM $tbl WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_old_esc' ",$dbh);
		$MSGS = array();
		if($res)
		{
			while($req = mysql_fetch_assoc($res))
			{
				$MSGS[] = $req;
			}
		}
		foreach($MSGS as $rec)
		{
			$SQL_AR = array();
			foreach($rec as $a=>$v)
			{
				if($a=='dtm')
				{
					list($s1,$s2) = explode('.',$v);
					$s2++;
					$v = $s1.'.'.$s2;
				}
				if($a=='server_uniq') $v = $server_uniq_new_esc;
				if($a=='msg_userid' && $v<=0)
				{
					$v = $_SERVER['uinfo']['userid'];
				}
				$SQL_AR[] = $a.'="'.mysql_real_escape_string($v,$dbh).'"';
			}
			mysql_query("INSERT INTO $tbl SET ".implode(' , ',$SQL_AR),$dbh);
		}
		mysql_query("UPDATE $tbl SET client_uniq='$temp_client_uniq' WHERE client_uniq='$client_uniq_esc' AND server_uniq='$server_uniq_old_esc' ",$dbh);
		mysql_close($dbh);
	}
	function GetTransferListTree()
	{
		$RV['status'] = 1;
		$ulist_temp = GetUsersList($_SERVER['sinfo']['siteid']);
		$ulist_busy = $this->GetBusyUsers();
		$ulist = array();
		foreach($ulist_temp as $a=>$v)
		{
			if($v['last_uact']<time()-600)
			{
				$v['_lv_int_user_offline'] = 'y';
				$v['_lv_int_user_trans'] = 'n';
			}
			else
			{
				$v['_lv_int_user_offline'] = 'n';
				$v['_lv_int_user_trans'] = 'y';
			}
			if(isset($ulist_busy[$v['userid']])) $v['_lv_int_user_busy'] = 'y';
			else $v['_lv_int_user_busy'] = 'n';
			$v['_lv_int_user_you'] = 'n';
			if($v['userid']==$_SERVER['uinfo']['userid'])
			{
				$v['_lv_int_user_trans'] = 'n';
				$v['_lv_int_user_you'] = 'y';
			}
			$ulist[$v['userid']] = $v;
		}
		$departments = lv_get_departments($_SERVER['sinfo']);
		if(count($departments)>0)
		{
			$RV['list_type'] = 'da';
			$RV['list'] = array();
			$list_index = 0;
			foreach($departments as $depid=>$department)
			{
				$RV['list'][$list_index] = array ( 'name' => $department['name'], 'type' => 'd', 'id' => -1, 'depid' => $depid, 'trans' => 'n', 'free' => 0, 'total' => 0 );
				$last_dept_index = $list_index;
				$list_index++;
				$dept_can_get_transfer = 'n';
				$free_agents_count = 0;
				$total_agents_count = 0;
				foreach($department['agents'] as $userid=>$v)
				{
					if(!isset($ulist[$userid])) continue;
					$user_title = $ulist[$userid]['nickname'].' ('.$ulist[$userid]['username'].')';
					$RV['list'][$list_index] = array ( 'name' => $user_title, 'type' => 'a', 'id' => $userid, 'depid' => $depid, 'off' => $ulist[$userid]['_lv_int_user_offline'], 'busy' => $ulist[$userid]['_lv_int_user_busy'], 'trans' => $ulist[$userid]['_lv_int_user_trans'], 'you' => $ulist[$userid]['_lv_int_user_you'] );
					if($ulist[$userid]['_lv_int_user_trans']=='y')
					{
						$dept_can_get_transfer = 'y';
						$free_agents_count++;
					}
					$total_agents_count++;
					$list_index++;
				}
				$RV['list'][$last_dept_index]['trans'] = $dept_can_get_transfer;
				$RV['list'][$last_dept_index]['free'] = $free_agents_count;
				$RV['list'][$last_dept_index]['total'] = $total_agents_count;
			}
		}
		else
		{
			$RV['list_type'] = 'a';
			foreach($ulist as $a=>$v)
			{
				$user_title = $v['nickname'].' ('.$v['username'].')';
				$RV['list'][] = array ( 'name' => $user_title, 'type' => 'a', 'id' => $v['userid'], 'depid' => -1, 'off' => $v['_lv_int_user_offline'], 'busy' => $v['_lv_int_user_busy'], 'trans' => $v['_lv_int_user_trans'], 'you' => $v['_lv_int_user_you'] );
			}
		}
		return($RV);
	}
	function ChatTransfer()
	{
		$client_uniq = trim($_REQUEST['client_uniq']);
		$lv_type = trim($_REQUEST['lv_type']);
		$lv_id = trim($_REQUEST['lv_id']);
		$lv_depid = trim($_REQUEST['lv_depid']);
		$RV = array();
		$RV['status']=1;
		$RV['trans_status'] = 0;
		$RV['trans_error'] = 'Unable to transfer the client';
		switch($lv_type)
		{
			case 'a': $winfo = $this->GetWaitInfoByClientUniq($client_uniq);
			$new_uinfo = GetUserInfo('userid',$lv_id);
			if($new_uinfo===false)
			{
				$RV['trans_status'] = 0;
				$RV['trans_error'] = 'Unable to find the requested used';
				return($RV);
			}
			if($winfo['client_flag']!=4 && $winfo['client_flag']!=6)
			{
				$RV['trans_status'] = 0;
				$RV['trans_error'] = 'Chat session of this client is not active and can not be transferred';
				return($RV);
			}
			if($winfo['server_userid']!=$_SERVER['uinfo']['userid'])
			{
				$RV['trans_status'] = 0;
				$RV['trans_error'] = 'Chat session of this client is not yours, transfer is not possible';
				return($RV);
			}
			$client_info_dec = liveadmin_decode64(unserialize($winfo['client_info']));
			$client_info_dec['_lv_server_uniq'] = RandomString(32);
			if(isset($client_info_dec['_lv_department']))
			{
				$client_info_dec['_lv_department'] = $lv_depid;
			}
			$client_info_dec['_lv_excl_userid'] = $new_uinfo['userid'];
			$client_info = array();
			foreach($client_info_dec as $a=>$v)
			{
				$client_info[$a] = liveadmin_encode64($v);
			}
			$new_client_info = serialize($client_info);
			$this->PostNewMessage('<s5lang>Chat transferred to</s5lang> '.$new_uinfo['nickname'],$client_uniq,$winfo['server_uniq'],3);
			$this->PostNewMessage('<s5lang>A102200</s5lang> '.$new_uinfo['nickname'],$client_uniq,$winfo['server_uniq'],4);
			$temp_client_uniq = RandomString(32);
			$this->UpdateWaitListForTransfer($winfo,$client_uniq,$temp_client_uniq,$new_client_info,$winfo['server_uniq'],$client_info_dec['_lv_server_uniq']);
			$this->PostNewMessage(TRANSFER_DEPT,$client_uniq,$winfo['server_uniq'],6);
			$this->CloseChat($temp_client_uniq,$winfo['server_uniq'],$winfo['server_nickname']);
			$RV['trans_status'] = 1;
			$RV['trans_error'] = '';
			$RV['trans_msg'] = array ( 'msg' => '<s5lang>Chat transferred to</s5lang> '.$new_uinfo['nickname'], 'dir' => 'svc', 'time' => lv_date('H:i:s',intval(time()),$_SERVER['uinfo']['time_zone']) );
			break;
			case 'd': $winfo = $this->GetWaitInfoByClientUniq($client_uniq);
			if($winfo['client_flag']!=4 && $winfo['client_flag']!=6)
			{
				$RV['trans_status'] = 0;
				$RV['trans_error'] = 'Chat session of this client is not active and can not be transferred';
				return($RV);
			}
			if($winfo['server_userid']!=$_SERVER['uinfo']['userid'])
			{
				$RV['trans_status'] = 0;
				$RV['trans_error'] = 'Chat session of this client is not yours, transfer is not possible';
				return($RV);
			}
			$client_info_dec = liveadmin_decode64(unserialize($winfo['client_info']));
			if(!isset($client_info_dec['_lv_department']))
			{
				$RV['trans_status'] = 0;
				$RV['trans_error'] = 'Unable to determine current department of client, transfer is not possible';
				return($RV);
			}
			$client_info_dec['_lv_department'] = $lv_depid;
			$client_info_dec['_lv_server_uniq'] = RandomString(32);
			$departments = lv_get_departments($_SERVER['sinfo']);
			$client_info = array();
			foreach($client_info_dec as $a=>$v)
			{
				$client_info[$a] = liveadmin_encode64($v);
			}
			$new_client_info = serialize($client_info);
			$this->PostNewMessage('<s5lang>Chat transferred to</s5lang> '.$departments[$lv_depid]['name'],$client_uniq,$winfo['server_uniq'],3);
			$this->PostNewMessage('<s5lang>A102200</s5lang> '.$departments[$lv_depid]['name'],$client_uniq,$winfo['server_uniq'],4);
			$temp_client_uniq = RandomString(32);
			$this->UpdateWaitListForTransfer($winfo,$client_uniq,$temp_client_uniq,$new_client_info,$winfo['server_uniq'],$client_info_dec['_lv_server_uniq']);
			$this->PostNewMessage(TRANSFER_DEPT,$client_uniq,$winfo['server_uniq'],6);
			$this->CloseChat($temp_client_uniq,$winfo['server_uniq'],$winfo['server_nickname']);
			$RV['trans_status'] = 1;
			$RV['trans_error'] = '';
			$RV['trans_msg'] = array ( 'msg' => '<s5lang>Chat transferred to</s5lang> '.$departments[$lv_depid]['name'], 'dir' => 'svc', 'time' => lv_date('H:i:s',intval(time()),$_SERVER['uinfo']['time_zone']) );
			break;
		}
		return($RV);
	}
	function SyncDatabases()
	{
		if(LIVEADMIN_STANDALONE)
		{
			include_once('update.php');
			$lv_update = new LV_LiveAdminUpdate();
			$lv_update->SyncDatabases();
			unset($lv_update);
		}
	}
}
?>