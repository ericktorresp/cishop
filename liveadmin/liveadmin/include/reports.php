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
class LV_Reports
{
	private $lv_admin;
	function LV_Reports(&$lv_admin_in)
	{
		$this->lv_admin = $lv_admin_in;
	}
	function GetReport($report)
	{
		$RV = array();
		$dbh=mysql_connect(LIVEADMIN_DB_HOST, LIVEADMIN_DB_USER,LIVEADMIN_DB_PASS,true) or die ('0');
		mysql_select_db(LIVEADMIN_DB_DATABASE,$dbh);
		$tbl = LIVEADMIN_DB_PREFIX.'chat_arch_'.$_SERVER['sinfo']['siteid'];
		switch($report)
		{
			case 'reports_agents_average_answering_time': if(!isset($_REQUEST['date_range'])) $_REQUEST['date_range'] = 1;
			$date_range = $_REQUEST['date_range'];
			$sdate = time();
			$sdate = mktime(date("H",$sdate),date("i",$sdate),date("s",$sdate),date("m",$sdate)-$date_range,date("d",$sdate),date("Y",$sdate));
			$QU = "SELECT ";
			$QU .= "lvad_users.userid, ";
			$QU .= "lvad_users.nickname, ";
			$QU .= "Sum(chat_arch.end_date- chat_arch.start_date) AS sum_resp_time, ";
			$QU .= "Avg(chat_arch.end_date- chat_arch.start_date) AS avg_resp_time, ";
			$QU .= "Count(chat_arch.chatid) AS chat_count ";
			$QU .= "FROM ";
			$QU .= "$tbl AS chat_arch ";
			$QU .= "Inner Join lvad_users ON lvad_users.userid = chat_arch.userid ";
			$QU .= "WHERE end_date>$sdate ";
			$QU .= "GROUP BY ";
			$QU .= "lvad_users.userid, ";
			$QU .= "lvad_users.nickname ";
			$QU .= "ORDER BY ";
			$QU .= "lvad_users.userid ASC ";
			$res = mysql_query($QU,$dbh);
			if($res)
			{
				while($req = mysql_fetch_array($res))
				{
					$RV[] = array ( 'userid'=>$req['userid'], 'nickname'=>$req['nickname'], 'sum_resp_time'=>number_format($req['sum_resp_time']/60,0,'.',''), 'avg_resp_time'=>number_format($req['avg_resp_time']/60,0,'.',''), 'chat_count'=>$req['chat_count'] );
				}
			}
			break;
			case 'reports_requests_count': if(!isset($_REQUEST['report_mode'])) $_REQUEST['report_mode'] = 'daily';
			$sdate = time();
			$cdt = array ( 'hour'=>date("H",$sdate), 'minute'=>date("i",$sdate), 'second'=>date("s",$sdate), 'month'=>date("m",$sdate), 'day'=>date("d",$sdate), 'year'=>date("Y",$sdate) );
			switch($_REQUEST['report_mode'])
			{
				default: case 'daily': $sdate_esc = mktime($cdt['hour'],$cdt['minute'],$cdt['sec'],$cdt['month']-1,$cdt['day'],$cdt['year']);
				$QU = "SELECT ";
				$QU .= "Count(chat_arch.chatid) AS chat_count, ";
				$QU .= "Sum(chat_arch.end_date-chat_arch.start_date) AS sum_resp_time, ";
				$QU .= "Avg(chat_arch.end_date- chat_arch.start_date) AS avg_resp_time, ";
				$QU .= "DATE_FORMAT(FROM_UNIXTIME(chat_arch.end_date),\"%Y%m%d\") AS dt ";
				$QU .= "FROM ";
				$QU .= "$tbl AS chat_arch ";
				$QU .= "WHERE ";
				$QU .= "chat_arch.end_date >  $sdate_esc ";
				$QU .= "AND ";
				$QU .= "chat_arch.end_date-chat_arch.start_date>0 ";
				$QU .= "GROUP BY ";
				$QU .= "dt ";
				$QU .= "ORDER BY ";
				$QU .= "dt ";
				$res = mysql_query($QU,$dbh);
				$QU = "SELECT ";
				$QU .= "Count(chat_arch.chatid) AS missed_count, ";
				$QU .= "DATE_FORMAT(FROM_UNIXTIME(chat_arch.end_date),\"%Y%m%d\") AS dt ";
				$QU .= "FROM ";
				$QU .= "$tbl AS chat_arch ";
				$QU .= "WHERE ";
				$QU .= "chat_arch.end_date >  $sdate_esc ";
				$QU .= "AND ";
				$QU .= "chat_arch.end_date-chat_arch.start_date=0 ";
				$QU .= "GROUP BY ";
				$QU .= "dt ";
				$QU .= "ORDER BY ";
				$QU .= "dt ";
				$res2= mysql_query($QU,$dbh);
				for($i=$sdate_esc;$i<=$sdate;$i+=(24*3600))
				{
					$RV[date("Ymd",$i)] = array('sum_resp_time'=>0,'avg_resp_time'=>0,'chat_count'=>0,'missed_count'=>0,'dt'=>date("M j",$i),'dts'=>$i);
				}
				break;
				case 'monthly': $sdate_esc = mktime($cdt['hour'],$cdt['minute'],$cdt['sec'],$cdt['month'],$cdt['day'],$cdt['year']-1);
				$QU = "SELECT ";
				$QU .= "Count(chat_arch.chatid) AS chat_count, ";
				$QU .= "Sum(chat_arch.end_date-chat_arch.start_date) AS sum_resp_time, ";
				$QU .= "Avg(chat_arch.end_date- chat_arch.start_date) AS avg_resp_time, ";
				$QU .= "DATE_FORMAT(FROM_UNIXTIME(chat_arch.end_date),\"%Y%m\") AS dt ";
				$QU .= "FROM ";
				$QU .= "$tbl AS chat_arch ";
				$QU .= "WHERE ";
				$QU .= "chat_arch.end_date >  $sdate_esc ";
				$QU .= "AND ";
				$QU .= "chat_arch.end_date-chat_arch.start_date>0 ";
				$QU .= "GROUP BY ";
				$QU .= "dt ";
				$QU .= "ORDER BY ";
				$QU .= "dt ";
				$res = mysql_query($QU,$dbh);
				$QU = "SELECT ";
				$QU .= "Count(chat_arch.chatid) AS missed_count, ";
				$QU .= "DATE_FORMAT(FROM_UNIXTIME(chat_arch.end_date),\"%Y%m\") AS dt ";
				$QU .= "FROM ";
				$QU .= "$tbl AS chat_arch ";
				$QU .= "WHERE ";
				$QU .= "chat_arch.end_date >  $sdate_esc ";
				$QU .= "AND ";
				$QU .= "chat_arch.end_date-chat_arch.start_date=0 ";
				$QU .= "GROUP BY ";
				$QU .= "dt ";
				$QU .= "ORDER BY ";
				$QU .= "dt ";
				$res2 = mysql_query($QU,$dbh);
				for($i=12;$i>=0;$i--)
				{
					$idf = mktime($cdt['hour'],$cdt['minute'],$cdt['sec'],$cdt['month']-$i,$cdt['day'],$cdt['year']);
					$RV[date("Ym",$idf)] = array('sum_resp_time'=>0,'avg_resp_time'=>0,'chat_count'=>0,'missed_count'=>0,'dt'=>date("M Y",$idf),'dts'=>$idf);
				}
				break;
			}
			if($res)
			{
				while($req = mysql_fetch_array($res))
				{
					$RV[$req['dt']]['sum_resp_time']=number_format($req['sum_resp_time']/60,0,'.','');
					$RV[$req['dt']]['avg_resp_time']=number_format($req['avg_resp_time']/60,0,'.','');
					$RV[$req['dt']]['chat_count']=$req['chat_count'];
				}
			}
			if($res2)
			{
				while($req = mysql_fetch_array($res2))
				{
					$RV[$req['dt']]['missed_count']=$req['missed_count'];
				}
			}
			$RV2 = array();
			foreach($RV as $a=>$v)
			{
				$RV2[] = $v;
			}
			$RV = $RV2;
			break;
		}
		mysql_close($dbh);
		return(array('status'=>1,'list'=>$RV));
	}
}
?>