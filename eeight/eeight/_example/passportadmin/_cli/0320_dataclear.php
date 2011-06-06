<?php
/**
 * 路径: /_cli/0320_dataclear.php
 * 功能: 数据清理计算程序 ( * Crontab * )
 * 
 * 调用方式: dataclear.php i   (i:为日志清理类型)
 * ----------------------------------------------
 *   1、管理员日志清理
 *   2、用户日志清理
 *   3、帐变清理
 * 
 * 由操作系统调用的 dataclear.php
 * @author Saul
 */

define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件
set_time_limit(0);
//@ini_set( "display_errors", TRUE);
//error_reporting(E_ALL);

// 1, 安全过滤 ----------------------------------------------------------
if( !empty($_GET) || !empty($_POST) || !empty($_REQUEST) || empty($argv) )
{
    die('Error');  // 禁止网页 URL 形式调用
}

if( $argc > 2 )
{
	die("Error: Params more than 1\n");
}

//2、检查参数合法性
if( !isset($argv) || !is_numeric($argv[1]) )
{
	echo ("The Params is integer\n");
	echo ("1 AdminLog Clear\n");
	echo ("2 UserLog Clear\n");
	echo ("3 Orders Clear\n");
	echo ("5 MsgContent Clear\n");
	//echo ("6 MsgList Clear\n");
	echo ("7 ReportCount Clear\n");
	echo ("8 BankSnapshot Clear\n");
	echo ("9 OnlineLoad Clear\n");
	echo ("10 PaymentOut Clear\n");
	die();
}

$i = intval($argv[1]);

//3、系统初始化
switch( $i ) 
{
	case 1://管理员日志清理
    {
		$oConfig = new model_config();
		$configs = $oConfig->getConfigs(array('logcleardate','logclearstarttime','logclearendtime','logclearrun')); 
		if ($configs["logclearrun"]==0)
		{ // 基本配置.运行配置.日志是否清理 (1:开启, 0:关闭)
			die("This Programm is not allowed #1");
		}
		if( $configs["logclearstarttime"] > $configs["logclearendtime"] )
		{//对跨天的支持
			$now =date("Y-m-d H:i:s");
			$starttime = date("Y-m-d ").$configs["logclearstarttime"];
			$endtime = date("Y-m-d ").$configs["logclearendtime"];
			if (($now < $starttime)&&($endtime>$now))
			{
				die("Now is not allow Programm run. #2");
			}
		}
		else
		{//没有跨天的支持
			$now = date("H:i:s");
			if($now<$configs["logclearstarttime"])
			{
				die("Now is not allow Programm run. #3");
			}
			if ($now > $configs["logclearendtime"])
			{
				die("Now is not allow Programm run. #4");
			}
		}
		$day = $configs["logcleardate"];
		$File = date("Ymd")."_admin.gz";
		$Path = PDIR.DS."_data".DS."adminlog".DS;
		$oAdminlog = new model_adminlog();
		makeDir($Path);
		$oAdminlog->bakLog($day,$Path.$File);
		die( "[d] [".date('Y-m-d H:i:s'). " Admin Log had Clear\n");
		break;
    }
	case 2://用户日志清理
	{
		$oConfig = new model_config();
		$configs = $oConfig->getConfigs(array('logcleardate','logclearstarttime','logclearendtime','logclearrun')); 
		if ($configs["logclearrun"]==0)
		{ // 基本配置.运行配置.日志是否清理 (1:开启, 0:关闭)
			die("This Programm is not allowed #10");
		}
		if ($configs["logclearstarttime"]>$configs["logclearendtime"])
		{//对跨天的支持
			$now =date("Y-m-d H:i:s");
			$starttime = date("Y-m-d ").$configs["logclearstarttime"];
			$endtime = date("Y-m-d ").$configs["logclearendtime"];
			if (($now < $starttime)&&($endtime>$now))
			{
				die("Now is not allow Programm run. #11");
			}
		}
		else
		{//没有跨天的支持
			$now = date("H:i:s");
			if($now<$configs["logclearstarttime"])
			{
				die("Now is not allow Programm run. #12");
			}
			if ($now > $configs["logclearendtime"])
			{
				die("Now is not allow Programm run. #13");
			}
		}
		$day = $configs["logcleardate"];
		$File = date("Ymd")."_user.gz";
		$Path = PDIR.DS."_data".DS."userlog".DS; 
		$oUserlog = new model_userlog();
		makeDir($Path);
		$oUserlog->bakLog($day,$Path.$File);
		die( "[d] [".date('Y-m-d H:i:s'). " User Log had Clear\n");
		break;
	}
	case 3://帐变清理
	{
		$oConfig = new model_config();
		$configs = $oConfig->getConfigs(array('orderscleardate','ordersclearstarttime','ordersclearendtime','ordersclearrun')); 
		if ($configs["ordersclearrun"]==0)
		{ // 基本配置.运行配置.日志是否清理 (1:开启, 0:关闭)
			die("This Programm is not allowed #20");
		}
		if ($configs["ordersclearstarttime"]>$configs["ordersclearendtime"])
		{//对跨天的支持
			$now =date("Y-m-d H:i:s");
			$starttime = date("Y-m-d ").$configs["ordersclearstarttime"];
			$endtime = date("Y-m-d ").$configs["ordersclearendtime"];
			if (($now < $starttime)&&($endtime>$now))
			{
				die("Now is not allow Programm run. #21");
			}
		}
		else
		{//没有跨天的支持
			$now = date("H:i:s");
			if($now<$configs["ordersclearstarttime"])
			{
				die("Now is not allow Programm run. #22");
			}
			if ($now > $configs["ordersclearendtime"])
			{
				die("Now is not allow Programm run. #23");
			}
		}
		$day = $configs["orderscleardate"];
		$File = date("Ymd")."_orders.gz";
		$Path = PDIR.DS."_data".DS."orders".DS;
		$oOrders = new model_orders();
		makeDir($Path);
		$oOrders->bakLog($day,$Path.$File);
		die( "[d] [".date('Y-m-d H:i:s'). " Orders had Clear\n");
		break;
	}
	
	/* 6/21/2010 */
	case 5:
	{
		//msgcontent 消息内容表清理
		$oConfig = new model_config();
		$configs = $oConfig->getConfigs(array('msgcontentcleardate','msgcontentclearstarttime','msgcontentclearendtime','msgcontentclearrun')); 
		if ($configs["msgcontentclearrun"]==0)
		{ // 基本配置.运行配置.日志是否清理 (1:开启, 0:关闭)
			die("This Programm is not allowed #50");
		}
		if ($configs["msgcontentclearstarttime"]>$configs["msgcontentclearendtime"])
		{//对跨天的支持
			$now =date("Y-m-d H:i:s");
			$starttime = date("Y-m-d ").$configs["msgcontentclearstarttime"];
			$endtime = date("Y-m-d ").$configs["msgcontentclearendtime"];
			if (($now < $starttime)&&($endtime>$now))
			{
				die("Now is not allow Programm run. #51");
			}
		}
		else
		{//没有跨天的支持
			$now = date("H:i:s");
			if($now<$configs["msgcontentclearstarttime"])
			{
				die("Now is not allow Programm run. #52");
			}
			if ($now > $configs["msgcontentclearendtime"])
			{
				die("Now is not allow Programm run. #53");
			}
		}
		$day = $configs["msgcontentcleardate"];
		$File = date("Ymd")."_msgcontent.gz";
		$Path = PDIR.DS."_data".DS."msgcontent".DS;
		$oMsg = new model_message();
		makeDir($Path);
		$oMsg->backandclearData($day,$Path);
		die( "[d] [".date('Y-m-d H:i:s'). " Msgcontent had Clear\n");
		break;
	}
	
	/*case 6:
	{
		//msglist 用户消息关系表
		$oConfig = new model_config();
		$configs = $oConfig->getConfigs(array('msglistcleardate','msglistclearstarttime','msglistclearendtime','msglistclearrun')); 
		if ($configs["msglistclearrun"]==0)
		{ // 基本配置.运行配置.日志是否清理 (1:开启, 0:关闭)
			die("This Programm is not allowed #60");
		}
		if ($configs["msglistclearstarttime"]>$configs["msglistclearendtime"])
		{//对跨天的支持
			$now =date("Y-m-d H:i:s");
			$starttime = date("Y-m-d ").$configs["msglistclearstarttime"];
			$endtime = date("Y-m-d ").$configs["msglistclearendtime"];
			if (($now < $starttime)&&($endtime>$now))
			{
				die("Now is not allow Programm run. #61");
			}
		}
		else
		{//没有跨天的支持
			$now = date("H:i:s");
			if($now<$configs["msglistclearstarttime"])
			{
				die("Now is not allow Programm run. #62");
			}
			if ($now > $configs["msglistclearendtime"])
			{
				die("Now is not allow Programm run. #63");
			}
		}
		$day = $configs["msglistcleardate"];
		$File = date("Ymd")."_msglist.gz";
		$Path = PDIR.DS."_data".DS."msglist".DS;
		$oMsglist = new model_message();
		makeDir($Path);
		$oMsglist->backandclearDataList($day,$Path);
		die( "[d] [".date('Y-m-d H:i:s'). " Msglist had Clear\n");
		break;
	}*/
	
	case 7:
	{
		//reportcount 统计报表历史数据
		$oConfig = new model_config();
		$configs = $oConfig->getConfigs(array('reportcountcleardate','reportcountclearstarttime','reportcountclearendtime','reportcountclearrun')); 
		if ($configs["reportcountclearrun"]==0)
		{ // 基本配置.运行配置.日志是否清理 (1:开启, 0:关闭)
			die("This Programm is not allowed #70");
		}
		if ($configs["reportcountclearstarttime"]>$configs["reportcountclearendtime"])
		{//对跨天的支持
			$now =date("Y-m-d H:i:s");
			$starttime = date("Y-m-d ").$configs["reportcountclearstarttime"];
			$endtime = date("Y-m-d ").$configs["reportcountclearendtime"];
			if (($now < $starttime)&&($endtime>$now))
			{
				die("Now is not allow Programm run. #71");
			}
		}
		else
		{//没有跨天的支持
			$now = date("H:i:s");
			if($now<$configs["reportcountclearstarttime"])
			{
				die("Now is not allow Programm run. #72");
			}
			if ($now > $configs["reportcountclearendtime"])
			{
				die("Now is not allow Programm run. #73");
			}
		}
		$day = $configs["reportcountcleardate"];
		$File = date("Ymd")."_reportcount.gz";
		$Path = PDIR.DS."_data".DS."reportcount".DS;
		$oReportCount = new model_charts();
		makeDir($Path);
		$oReportCount->backandclearReportCount($day,$Path);
		die( "[d] [".date('Y-m-d H:i:s'). " ReportCount had Clear\n");
		break;
	}
	
	case 8:
	{
		//banksnapshot 银行快照记录表
		$oConfig = new model_config();
		$configs = $oConfig->getConfigs(array('banksnapshotcleardate','banksnapshotclearstarttime','banksnapshotclearendtime','banksnapshotclearrun')); 
		if ($configs["banksnapshotclearrun"]==0)
		{ // 基本配置.运行配置.日志是否清理 (1:开启, 0:关闭)
			die("This Programm is not allowed #80");
		}
		if ($configs["banksnapshotclearstarttime"]>$configs["banksnapshotclearendtime"])
		{//对跨天的支持
			$now =date("Y-m-d H:i:s");
			$starttime = date("Y-m-d ").$configs["banksnapshotclearstarttime"];
			$endtime = date("Y-m-d ").$configs["banksnapshotclearendtime"];
			if (($now < $starttime)&&($endtime>$now))
			{
				die("Now is not allow Programm run. #81");
			}
		}
		else
		{//没有跨天的支持
			$now = date("H:i:s");
			if($now<$configs["banksnapshotclearstarttime"])
			{
				die("Now is not allow Programm run. #82");
			}
			if ($now > $configs["banksnapshotclearendtime"])
			{
				die("Now is not allow Programm run. #83");
			}
		}
		$day = $configs["banksnapshotcleardate"];
		$File = date("Ymd")."_banksnapshot.gz";
		$Path = PDIR.DS."_data".DS."banksnapshot".DS;
		$obanksnapshot = new model_banksnapshot();
		makeDir($Path);
		$obanksnapshot->backandclear($day,$Path);
		die( "[d] [".date('Y-m-d H:i:s'). " BankSnapshot had Clear\n");
		break;
	}
	
	case 9:
	{
		//onlineload 在线充值相关表
		$oConfig = new model_config();
		$configs = $oConfig->getConfigs(array('onlineloadcleardate','onlineloadclearstarttime','onlineloadclearendtime','onlineloadclearrun')); 
		if ($configs["onlineloadclearrun"]==0)
		{ // 基本配置.运行配置.日志是否清理 (1:开启, 0:关闭)
			die("This Programm is not allowed #90");
		}
		if ($configs["onlineloadclearstarttime"]>$configs["onlineloadclearendtime"])
		{//对跨天的支持
			$now =date("Y-m-d H:i:s");
			$starttime = date("Y-m-d ").$configs["onlineloadclearstarttime"];
			$endtime = date("Y-m-d ").$configs["onlineloadclearendtime"];
			if (($now < $starttime)&&($endtime>$now))
			{
				die("Now is not allow Programm run. #91");
			}
		}
		else
		{//没有跨天的支持
			$now = date("H:i:s");
			if($now<$configs["onlineloadclearstarttime"])
			{
				die("Now is not allow Programm run. #92");
			}
			if ($now > $configs["onlineloadclearendtime"])
			{
				die("Now is not allow Programm run. #93");
			}
		}
		$day = $configs["onlineloadcleardate"];
		$File = date("Ymd")."_onlineload.gz";
		$Path = PDIR.DS."_data".DS."onlineload".DS;
		$oonlineload = new model_paymentclear();
		makeDir($Path);
		$oonlineload->backandclear($day,$Path);
		die( "[d] [".date('Y-m-d H:i:s'). " OnlineLoad had Clear\n");
		break;
	}
	
	case 10:
	{
		//payout 在线充值相关表
		$oConfig = new model_config();
		$configs = $oConfig->getConfigs(array('payoutcleardate','payoutclearstarttime','payoutclearendtime','payoutclearrun')); 
		if ($configs["payoutclearrun"]==0)
		{ // 基本配置.运行配置.日志是否清理 (1:开启, 0:关闭)
			die("This Programm is not allowed #100");
		}
		if ($configs["payoutclearstarttime"]>$configs["payoutclearendtime"])
		{//对跨天的支持
			$now =date("Y-m-d H:i:s");
			$starttime = date("Y-m-d ").$configs["payoutclearstarttime"];
			$endtime = date("Y-m-d ").$configs["payoutclearendtime"];
			if (($now < $starttime)&&($endtime>$now))
			{
				die("Now is not allow Programm run. #101");
			}
		}
		else
		{//没有跨天的支持
			$now = date("H:i:s");
			if($now<$configs["payoutclearstarttime"])
			{
				die("Now is not allow Programm run. #102");
			}
			if ($now > $configs["payoutclearendtime"])
			{
				die("Now is not allow Programm run. #103");
			}
		}
		$day = $configs["payoutcleardate"];
		$File = date("Ymd")."_payout.gz";
		$Path = PDIR.DS."_data".DS."payout".DS;
		$opayout = new model_paymentclear();
		makeDir($Path);
		$opayout->backandclearPayOut($day,$Path);
		die( "[d] [".date('Y-m-d H:i:s'). " PaymentOut had Clear\n");
		break;
	}
	
	case 4://用户清理
	{
		die("Programmer ing");
		break;	
	}
	default:
	{
		echo("Params is Wrong\nThe Params is follow:\n");
		echo ("1 AdminLog Clear\n");
		echo ("2 UserLog Clear\n");
		echo ("3 Orders Clear\n");
		//echo ("4 Users Clear\n");
		echo ("5 MsgContent Clear\n");
		//echo ("6 msglist Clear\n");
		echo ("7 reportcount Clear\n");
		echo ("8 BankSnapshot Clear\n");
		echo ("9 OnlineLoad Clear\n");
		echo ("10 PaymentOut Clear\n");
		die();
	}
	break;
}
?>