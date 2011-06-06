<?php
/**
 * 文件 : /_app/controller/account.php
 * 功能 : 控制器 - 数据管理
 * 
 *     - actionClear()              不活跃用户清理(设置)
 *     - actionUnactiveuserlist()   不活跃用户列表(查看)
 *     - actionCheck()              银行快照检查
 *     - actionResetzero()          负余额清零(前台)
 *     - actionDoresetzero()        负余额清零(执行)
 *     - actionAdjust()             差额调整(前台)
 *     - actionOrdersclear()        帐变清理(设置)
 *     - actionLogclear()           日志清理(设置)	
 *	   - actionreportclear			消息内容清理(设置)	[6/23/2010]
 *	   - actionreportclear 			统计报表历史数据清理(设置)	[6/23/2010]
 * 	   - actionbanksnapshotclear 	银行快照表清理(设置)	[6/23/2010]
 * 	   - actionpayoutclear 			在线提现清理(设置)	[6/23/2010]
 * 	   - actiononlineloadclear 		在线充值清理(设置)  [6/23/2010]
 *     - actionCheckOrder           转账失败检查
 *     - actionErrorOperation()     处理异常账变
 * 
 * @author	   Saul & Tom    090914
 * @version   1.2.0
 * @package   passportadmin
 */
class controller_account extends basecontroller 
{
	/**
	 * 不活跃用户清理(设置)
	 * URL = ./?controller=account&action=clear
	 * @author Saul
	 */
	function actionClear()
	{
        if( isset($_POST) && !empty($_POST) )
        {
            $iDay       = (isset($_POST["day"]) && is_numeric($_POST["day"])) ? intval($_POST["day"]) : 14;
            $fMinMoney  = (isset($_POST["min"]) && is_numeric($_POST["min"])) ? floatval($_POST["min"]): 0.00;
            $fMaxMoney  = (isset($_POST["max"]) && is_numeric($_POST["max"])) ? floatval($_POST["max"]): 0.00;
            $sActions   = !empty($_POST["actions"]) ? $_POST["actions"] : "";
            $iIsRun     = (isset($_POST["run"])&&is_numeric($_POST["run"])) ? intval($_POST["run"]) : 0;
            $sStartTime = "02:00:00";
            if( isset($_POST["start"]) )
            {
            	$_POST["start"] = getFilterDate($_POST["start"] ,"H:i:s");
            	$sStartTime     = !empty($_POST["start"]) ? $_POST["start"] : $sStartTime;
            }
            $sEndTime = "05:00:00";
            if( isset($_POST["end"]) )
            {
                $_POST["end"] = getFilterDate($_POST["end"] ,"H:i:s");
                $sEndTime     = !empty($_POST["end"]) ? $_POST["end"] : $sEndTime;
            }
            $oConfig    = new model_config();
            $aConfig    = array(
            	"unactiveclearday"		=>	$iDay,
            	"unactiveclearmincash"	=>	number_format($fMinMoney,2),
            	"unactiveclearmaxcash"	=>	number_format($fMaxMoney,2),
            	"unactiveclearrun"		=>	$iIsRun,
            	"unactiveclearstart"	=>	$sStartTime,
            	"unactiveclearend"		=>	$sEndTime,
            	"unactiveclearaction"	=>	$sActions
            );
            $aLocation  = array(0=>array('text'=>'返回: 不活跃用户清理','href'=>url('account','clear')));
            if( $oConfig->updateConfigs( $aConfig ) )
            {
            	sysMessage('操作成功', 0, $aLocation );
            }
            else 
            {
            	sysMessage('操作失败', 1, $aLocation );
            }
        }
        else 
        {
            // 不活跃用户后台界面 (显示页)
            $oConfig = new model_config();
            $aConfig = $oConfig->getConfigs( array(
                'unactiveclearday',          // 不活跃用户清理 天数       => 14
                'unactiveclearmincash',      // 不活跃用户清理 最小金额   => 0.00
                'unactiveclearmaxcash',      // 不活跃用户清理 最大金额   => 10.00
                'unactiveclearrun',          // 不活跃用户清理 是否执行   => 1
                'unactiveclearstart',        // 不活跃用户清理 执行最早时间=> 02:00:00
                'unactiveclearend',          // 不活跃用户清理 执行最晚时间=> 05:00:00
                'unactiveclearaction'        // 不活跃用户清理 行为       => freeze
            ));
            $aAction = explode( "|", $aConfig["unactiveclearaction"] );
            if( in_array("delete",$aAction) ) // 逻辑删除
            {
            	$aConfig["delete"] = 1;
            }
            if( in_array("freeze",$aAction) ) // 冻结
            {
            	$aConfig["freeze"] = 1;
            }
            unset($aConfig["unactiveclearaction"]);
            $GLOBALS['oView']->assign("configs", $aConfig );
            $GLOBALS['oView']->assign('ur_here', '不活跃用户清理');
            $oConfig->assignSysInfo();
            $GLOBALS['oView']->display('account_clear.html');
            EXIT;
        }
    }



	/**
	 * 负余额清零(前台查看)
	 * URL = ./?controller=account&action=resetzero
	 * @author Saul
	 */
	function actionResetzero()
	{
        /* @var $oUser model_user */
		$oUser      = A::singleton('model_user');
		$aUserlist  = $oUser->getUnderZeroUser();
		$GLOBALS['oView']->assign("ur_here","负余额清零");
		$GLOBALS['oView']->assign("userlist",$aUserlist);
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("account_resetzero.html");
		EXIT;
	}



    /**
     * 负余额清零(执行)
     *    对提交数据(用户ID) 进行负余额清零操作(公司认赔操作)
     * URL = ./?controller=account&action=doresetzero
     * @author: Tom
     */
    function actionDoresetzero()
    {
        $aLocation = array( 0=>array('text'=>'返回: 负余额清零','href'=>url('account','resetzero')) );
        if( !isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']) )
        {
            sysMessage("未选择数据", 1);
        }
        /* @var $oUser model_user */
        $oUser = A::singleton('model_user');
        /**
         * 返回正数表示成功 '负余额清零的数量', 返回复数表示错误信息.
         */
        $mFlag = $oUser->doFixZeroUser( $_POST['checkboxes'] );
        if( $mFlag > 0 )
        {
            sysMessage("操作成功, 受影响用户数 [$mFlag]", 0, $aLocation );
        }
        else
        {
            sysMessage("操作失败", 1, $aLocation );
        }
    }



	/**
	 * 差额调整
	 *    注: 在开发第一期中, 此功能已被取消, 但还是允许查看
	 * URL = ./?controller=account&action=adjust
	 * @author Saul
	 */
	function actionAdjust()
	{
	    /* @var $oUserfund model_userfund */
		$oUserfund = A::singleton('model_userfund');
		$aUser     = $oUserfund->getErrorFund();
		if( !empty($aUser) )
		{
			$GLOBALS['oView']->assign( 'users', $aUser );
		}
		$GLOBALS['oView']->assign('ur_here','差额调整');
		$oUserfund->assignSysInfo();
		$GLOBALS['oView']->display("account_adjust.html");
		EXIT;
	}



	/**
	 * 帐变清理(设置)
	 * URL = ./?controller=account&action=ordersclear
	 * @author Saul
	 */
	function actionOrdersclear()
	{
        if( isset($_POST) && !empty($_POST) )
        {
            $iDay       = (isset($_POST["day"]) && is_numeric($_POST["day"])) ? intval($_POST["day"]) : 5;
            $iIsRun     = (isset($_POST["run"]) && is_numeric($_POST["run"])) ? intval($_POST["run"]) : 0;
            $sStartTime = getFilterDate($_POST["starttime"],"H:i:s") ? getFilterDate($_POST["starttime"],"H:i:s") : "02:00:00";
            $sEndTime   = getFilterDate($_POST["endtime"],"H:i:s")   ? getFilterDate($_POST["endtime"],"H:i:s")   : "03:00:00";
            /* @var $oConfig model_config */
            $oConfig    = A::singleton('model_config');
            $aConfig    = array(
            	"orderscleardate"      => $iDay,
            	"ordersclearstarttime" => $sStartTime,
            	"ordersclearendtime"   => $sEndTime,
            	"ordersclearrun"       => $iIsRun
            );
            $aLocation  = array(0=>array('text'=>'返回: 帐变清理', 'href'=>url('account','ordersclear')));
            if( $oConfig->updateConfigs($aConfig) )
            {
            	sysMessage('操作成功', 0, $aLocation);
            }
            else
            {
            	sysMessage('操作失败', 1, $aLocation);
            }
        }
        else
        {
            $oConfig = new model_config();
            $aConfig = $oConfig->getConfigs(
            			array('orderscleardate','ordersclearstarttime',
            				  'ordersclearendtime','ordersclearrun')
            		);
            $GLOBALS["oView"]->assign("day",       $aConfig["orderscleardate"]);
            $GLOBALS["oView"]->assign("starttime", $aConfig["ordersclearstarttime"]);			
            $GLOBALS["oView"]->assign("endtime",   $aConfig["ordersclearendtime"]);
            if(isset($aConfig["ordersclearrun"]))
            {
            	$GLOBALS['oView']->assign("run",   $aConfig["ordersclearrun"]);
            }
            $GLOBALS["oView"]->assign("ur_here",   "帐变清理");
            $GLOBALS['oView']->assign("action",    "ordersclear");
            $GLOBALS["oView"]->display("account_logclear.html");
            EXIT;
        }
    }



    
	/**
	 * 日志清理(设置)
	 * URL = ./?controller=account&action=logclear
	 * @author Saul
	 */
	function actionLogclear()
	{
        if( isset($_POST) && !empty($_POST) )
        {
            $iDay       = (isset($_POST["day"]) && is_numeric($_POST["day"]))? intval($_POST["day"]) : 5;
            $iIsRun     = (isset($_POST["run"]) && is_numeric($_POST["run"]))? intval($_POST["run"]) : 0;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"])?$_POST["endtime"]:"03:00:00";
            /* @var $oConfig model_config */
            $oConfig    = A::singleton('model_config');
            $aConfig    = array(
            	"logcleardate"      => $iDay,
            	"logclearstarttime" => $sStartTime,
            	"logclearendtime"   => $sEndTime,
            	"logclearrun"       => $iIsRun
            );
            $aLocation = array( 0=>array('text'=>'返回:日志清理','href'=>url('account','logclear')) );
            if( $oConfig->updateConfigs($aConfig) )
            {
            	sysMessage('操作成功', 0, $aLocation);
            }
            else
            {
            	sysMessage('操作失败', 1, $aLocation);
            }
        }
        else
        {
            $oConfig = new model_config();
            $aConfig = $oConfig->getConfigs(
            		array('logcleardate','logclearstarttime','logclearendtime','logclearrun')
            	);
            $GLOBALS["oView"]->assign( "day",       $aConfig["logcleardate"] );
            $GLOBALS["oView"]->assign( "starttime", $aConfig["logclearstarttime"] );			
            $GLOBALS["oView"]->assign( "endtime",   $aConfig["logclearendtime"] );
            if(isset($aConfig["logclearrun"]))
            {
            	$GLOBALS['oView']->assign("run",   $aConfig["logclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "日志清理");
            $GLOBALS['oView']->assign( "action",    "logclear");
            $GLOBALS["oView"]->display( "account_logclear.html");
            EXIT;
        }
    }


    
	/**
	 * 消息内容清理(设置)
	 * URL = ./?controller=account&action=reportclear
	 *  6/23/2010
	 */
	function actionMsgClear()
	{
        if( isset($_POST) && !empty($_POST) )
        {
            $iDay       = (isset($_POST["day"]) && is_numeric($_POST["day"]))? intval($_POST["day"]) : 5;
            $iIsRun     = (isset($_POST["run"]) && is_numeric($_POST["run"]))? intval($_POST["run"]) : 0;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"])?$_POST["endtime"]:"03:00:00";
            /* @var $oConfig model_config */
            $oConfig    = A::singleton('model_config');
            $aConfig    = array(
            	"msgcontentcleardate"      => $iDay,
            	"msgcontentclearstarttime" => $sStartTime,
            	"msgcontentclearendtime"   => $sEndTime,
            	"msgcontentclearrun"       => $iIsRun
            );
            $aLocation = array( 0=>array('text'=>'返回:消息内容清理(设置)','href'=>url('account','msgclear')) );
            if( $oConfig->updateConfigs($aConfig) )
            {
            	sysMessage('操作成功', 0, $aLocation);
            }
            else
            {
            	sysMessage('操作失败', 1, $aLocation);
            }
        }
        else
        {
            $oConfig = new model_config();
            $aConfig = $oConfig->getConfigs(
            		array('msgcontentcleardate','msgcontentclearstarttime','msgcontentclearendtime','msgcontentclearrun')
            	);
            $GLOBALS["oView"]->assign( "day",       $aConfig["msgcontentcleardate"] );
            $GLOBALS["oView"]->assign( "starttime", $aConfig["msgcontentclearstarttime"] );			
            $GLOBALS["oView"]->assign( "endtime",   $aConfig["msgcontentclearendtime"] );
            if(isset($aConfig["msgcontentclearrun"]))
            {
            	$GLOBALS['oView']->assign("run",   $aConfig["msgcontentclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "消息内容清理(设置)");
            $GLOBALS['oView']->assign( "action",    "msgclear");
            $GLOBALS["oView"]->display( "account_logclear.html");
            EXIT;
        }
    }
    
	/**
	 * 统计报表历史数据清理(设置)
	 * URL = ./?controller=account&action=reportclear
	 * 6/23/2010
	 */
	function actionReportClear()
	{
        if( isset($_POST) && !empty($_POST) )
        {
            $iDay       = (isset($_POST["day"]) && is_numeric($_POST["day"]))? intval($_POST["day"]) : 5;
            $iIsRun     = (isset($_POST["run"]) && is_numeric($_POST["run"]))? intval($_POST["run"]) : 0;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"])?$_POST["endtime"]:"03:00:00";
            /* @var $oConfig model_config */
            $oConfig    = A::singleton('model_config');
            $aConfig    = array(
            	"reportcountcleardate"      => $iDay,
            	"reportcountclearstarttime" => $sStartTime,
            	"reportcountclearendtime"   => $sEndTime,
            	"reportcountclearrun"       => $iIsRun
            );
            $aLocation = array( 0=>array('text'=>'返回:统计报表历史数据清理(设置)','href'=>url('account','reportclear')) );
            if( $oConfig->updateConfigs($aConfig) )
            {
            	sysMessage('操作成功', 0, $aLocation);
            }
            else
            {
            	sysMessage('操作失败', 1, $aLocation);
            }
        }
        else
        {
            $oConfig = new model_config();
            $aConfig = $oConfig->getConfigs(
            		array('reportcountcleardate','reportcountclearstarttime','reportcountclearendtime','reportcountclearrun')
            	);
            $GLOBALS["oView"]->assign( "day",       $aConfig["reportcountcleardate"] );
            $GLOBALS["oView"]->assign( "starttime", $aConfig["reportcountclearstarttime"] );			
            $GLOBALS["oView"]->assign( "endtime",   $aConfig["reportcountclearendtime"] );
            if(isset($aConfig["reportcountclearrun"]))
            {
            	$GLOBALS['oView']->assign("run",   $aConfig["reportcountclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "统计报表历史数据清理(设置)");
            $GLOBALS['oView']->assign( "action",    "reportclear");
            $GLOBALS["oView"]->display( "account_logclear.html");
            EXIT;
        }
    }
    
    
	/**
	 * 银行快照表清理(设置)
	 * URL = ./?controller=account&action=banksnapshotclear
	 * 6/23/2010
	 */
	function actionBankSnapshotClear()
	{
        if( isset($_POST) && !empty($_POST) )
        {
            $iDay       = (isset($_POST["day"]) && is_numeric($_POST["day"]))? intval($_POST["day"]) : 5;
            $iIsRun     = (isset($_POST["run"]) && is_numeric($_POST["run"]))? intval($_POST["run"]) : 0;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"])?$_POST["endtime"]:"03:00:00";
            /* @var $oConfig model_config */
            $oConfig    = A::singleton('model_config');
            $aConfig    = array(
            	"banksnapshotcleardate"      => $iDay,
            	"banksnapshotclearstarttime" => $sStartTime,
            	"banksnapshotclearendtime"   => $sEndTime,
            	"banksnapshotclearrun"       => $iIsRun
            );
            $aLocation = array( 0=>array('text'=>'返回:银行快照表清理(设置)','href'=>url('account','banksnapshotclear')) );
            if( $oConfig->updateConfigs($aConfig) )
            {
            	sysMessage('操作成功', 0, $aLocation);
            }
            else
            {
            	sysMessage('操作失败', 1, $aLocation);
            }
        }
        else
        {
            $oConfig = new model_config();
            $aConfig = $oConfig->getConfigs(
            		array('banksnapshotcleardate','banksnapshotclearstarttime','banksnapshotclearendtime','banksnapshotclearrun')
            	);
            $GLOBALS["oView"]->assign( "day",       $aConfig["banksnapshotcleardate"] );
            $GLOBALS["oView"]->assign( "starttime", $aConfig["banksnapshotclearstarttime"] );			
            $GLOBALS["oView"]->assign( "endtime",   $aConfig["banksnapshotclearendtime"] );
            if(isset($aConfig["banksnapshotclearrun"]))
            {
            	$GLOBALS['oView']->assign("run",   $aConfig["banksnapshotclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "银行快照表清理(设置)");
            $GLOBALS['oView']->assign( "action",    "banksnapshotclear");
            $GLOBALS["oView"]->display( "account_logclear.html");
            EXIT;
        }
    }

    
	/**
	 * 在线提现清理(设置)
	 * URL = ./?controller=account&action=payoutclear
	 * 6/23/2010
	 */
	function actionPayOutClear()
	{
        if( isset($_POST) && !empty($_POST) )
        {
            $iDay       = (isset($_POST["day"]) && is_numeric($_POST["day"]))? intval($_POST["day"]) : 5;
            $iIsRun     = (isset($_POST["run"]) && is_numeric($_POST["run"]))? intval($_POST["run"]) : 0;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"])?$_POST["endtime"]:"03:00:00";
            /* @var $oConfig model_config */
            $oConfig    = A::singleton('model_config');
            $aConfig    = array(
            	"payoutcleardate"      => $iDay,
            	"payoutclearstarttime" => $sStartTime,
            	"payoutclearendtime"   => $sEndTime,
            	"payoutclearrun"       => $iIsRun
            );
            $aLocation = array( 0=>array('text'=>'返回:在线提现清理(设置)','href'=>url('account','payoutclear')) );
            if( $oConfig->updateConfigs($aConfig) )
            {
            	sysMessage('操作成功', 0, $aLocation);
            }
            else
            {
            	sysMessage('操作失败', 1, $aLocation);
            }
        }
        else
        {
            $oConfig = new model_config();
            $aConfig = $oConfig->getConfigs(
            		array('payoutcleardate','payoutclearstarttime','payoutclearendtime','payoutclearrun')
            	);
            $GLOBALS["oView"]->assign( "day",       $aConfig["payoutcleardate"] );
            $GLOBALS["oView"]->assign( "starttime", $aConfig["payoutclearstarttime"] );			
            $GLOBALS["oView"]->assign( "endtime",   $aConfig["payoutclearendtime"] );
            if(isset($aConfig["payoutclearrun"]))
            {
            	$GLOBALS['oView']->assign("run",   $aConfig["payoutclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "在线提现清理(设置)");
            $GLOBALS['oView']->assign( "action",    "payoutclear");
            $GLOBALS["oView"]->display( "account_logclear.html");
            EXIT;
        }
    }
    
    
	/**
	 * 在线充值清理(设置)
	 * URL = ./?controller=account&action=onlineloadclear
	 * 6/23/2010
	 */
	function actionOnlineLoadClear()
	{
        if( isset($_POST) && !empty($_POST) )
        {
            $iDay       = (isset($_POST["day"]) && is_numeric($_POST["day"]))? intval($_POST["day"]) : 5;
            $iIsRun     = (isset($_POST["run"]) && is_numeric($_POST["run"]))? intval($_POST["run"]) : 0;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"])?$_POST["endtime"]:"03:00:00";
            /* @var $oConfig model_config */
            $oConfig    = A::singleton('model_config');
            $aConfig    = array(
            	"onlineloadcleardate"      => $iDay,
            	"onlineloadclearstarttime" => $sStartTime,
            	"onlineloadclearendtime"   => $sEndTime,
            	"onlineloadclearrun"       => $iIsRun
            );
            $aLocation = array( 0=>array('text'=>'返回:在线充值清理(设置)','href'=>url('account','onlineloadclear')) );
            if( $oConfig->updateConfigs($aConfig) )
            {
            	sysMessage('操作成功', 0, $aLocation);
            }
            else
            {
            	sysMessage('操作失败', 1, $aLocation);
            }
        }
        else
        {
            $oConfig = new model_config();
            $aConfig = $oConfig->getConfigs(
            		array('onlineloadcleardate','onlineloadclearstarttime','onlineloadclearendtime','onlineloadclearrun')
            	);
            $GLOBALS["oView"]->assign( "day",       $aConfig["onlineloadcleardate"] );
            $GLOBALS["oView"]->assign( "starttime", $aConfig["onlineloadclearstarttime"] );			
            $GLOBALS["oView"]->assign( "endtime",   $aConfig["onlineloadclearendtime"] );
            if(isset($aConfig["onlineloadclearrun"]))
            {
            	$GLOBALS['oView']->assign("run",   $aConfig["onlineloadclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "在线充值清理(设置)");
            $GLOBALS['oView']->assign( "action",    "onlineloadclear");
            $GLOBALS["oView"]->display( "account_logclear.html");
            EXIT;
        }
    }
    
	/**
	 * 不活跃用户列表
	 * TODO: 完善此功能
	 * URL = ./controller=account&action=unactiveuserlist
	 * @author ??
	 */
	function actionUnactiveuserlist()
	{
		die("不活跃用户列表");
	}



	/**
	 * 检查快照状态 FOR 网管部
	 *   可查询所有快照列表, 并根据系统差帐金额设置. 显示是否超过报警范围
	 * URL = ./controller=account&action=checksnapshot
	 * @author Tom
	 */
	function actionChecksnapshot()
	{
        // 01, 整理搜索条件
        $aSearch['dates'] = isset($_GET['dates']) ? $_GET['dates'] : -1;
        $aHtmlValue       = array();
        $aResult          = array();
        $oBankSnapshot    = new model_banksnapshot();
        $aHtmlValue['dayopts'] = $oBankSnapshot->getDistintDays( FALSE, $aSearch['dates'] );
        
        $aResult['data'] = array();  // 数据结果集
        $aResult['time'] = '';       // 更新时间
        
        if( $aSearch['dates'] != -1 )
        {
            $iFlag = $oBankSnapshot->checkSnapshotDatas( $aSearch['dates'] );
            $aHtmlValue['message'] = $aSearch['dates'];
            // 根据 iFlag 值对结果进行封装
            if( 1 == $iFlag )
            {
                $aHtmlValue['message'] .=  ' 无差帐 <br/>No ERROR, Thanks.';
            }
            elseif( 2 == $iFlag )
            {
                $aHtmlValue['message'] .=  ' 有差帐, 但在允许范围内 <br/>There are some ERRORs, But within the scope of our financial capacity. ';
            }
            else 
            {
                $aHtmlValue['message'] .=  ' 有差帐, 范围外,请立即电话通知财务. <br/>Big ERRORs ,Please call the ACCOUNTING DEPARTMENT immediately！！';
            }
            $aHtmlValue['dates'] = $aSearch['dates'];
            $aHtmlValue['iflag'] = $iFlag;
        }
        $GLOBALS['oView']->assign( 's', $aHtmlValue );
        $GLOBALS['oView']->assign( 'aDataList', $aResult['data'] ); // 数据分配
        $GLOBALS['oView']->assign( 'ur_here', '检查快照状态' );
        $oBankSnapshot->assignSysInfo();
        $GLOBALS['oView']->display("account_checksnapshot.html");
        EXIT;
	}





	/**
	 * 转账失败检查
	 * URL = ./controller=account&action=checkOrder
	 * @author mark
	 */
	function actionCheckOrder()
	{
        $sUserName		= isset($_GET['username']) ? daddslashes($_GET['username']) : '';
        $iOrderTypeId 	= isset($_GET['ordertypeid']) && is_numeric($_GET['ordertypeid']) ? intval($_GET['ordertypeid']) : 0;
        $sStarttime 	= isset($_GET['starttime']) ? $_GET['starttime'] : date("Y-m-d H:i:s",strtotime("-1 days"));
        $sEndtime		= isset($_GET['endtime']) ? $_GET['endtime'] : date("Y-m-d H:i:s",time());
        $pn         	= isset($_GET['pn']) ? intval($_GET['pn']) : 25;
        $p  			= isset($_GET['p'])  ? intval($_GET['p'])  : 0;
        $oUser = new model_user($GLOBALS['aSysDbServer']['report']);
        $iUserId = $oUser->getUseridByUsername( $sUserName );
        $aCondition = array( 
        					"userid"      => $iUserId,
        					"ordertypeid" => $iOrderTypeId,
        					"starttime"   => $sStarttime,
        					"endtime"     => $sEndtime
        );
        $oOrder = new model_orders($GLOBALS['aSysDbServer']['report']);
        $aErrorOrder = $oOrder->getErrorOrder( $aCondition, $pn, $p );
        $aOrderType = array(
        					ORDER_TYPE_YHZC 	=> "银行转出",
        					ORDER_TYPE_ZRYH 	=> "转入银行",
        					ORDER_TYPE_PDXEZR 	=> "频道小额转入"
        );
        $oPager = new pages( $aErrorOrder['affects'], $pn, 10 );
        $GLOBALS["oView"]->assign( "ur_here",      "转账失败检查" );
        $GLOBALS['oView']->assign( "aErrorOrder",  $aErrorOrder['results'] );
        $GLOBALS['oView']->assign( "starttime",    $sStarttime );
        $GLOBALS['oView']->assign( "endtime",      $sEndtime );
        $GLOBALS['oView']->assign( "ordertypeid",  $iOrderTypeId );
        $GLOBALS['oView']->assign( "username",     $sUserName );
        $GLOBALS['oView']->assign( "aOrderType",   $aOrderType );
        $GLOBALS['oView']->assign( "pageinfo",     $oPager->show() );
        $GLOBALS['oView']->assign( 'sSysTopMessage', $aErrorOrder['affects']);
        $iAutoReflushSec = 60;
        $GLOBALS['oView']->assign( 'sSysAutoReflushSec', $iAutoReflushSec);
        $GLOBALS['oView']->assign( 'sSysMetaMessage', 
	    				'<META http-equiv="REFRESH" CONTENT="'.$iAutoReflushSec.'" />'); // 自动刷新 for 财务
        $GLOBALS['oView']->assign( 'actionlink',   array( 'href'=>url("account","checkorder"), 
                                                'text'=>'清空过滤条件' ) );
        $oOrder->assignSysInfo();
        $GLOBALS['oView']->display("account_checkorder.html");
        EXIT;
	}
	
	
	
	 /**
     * 处理账变异常
     * URL:./index.php?controller=account&action=erroroperation
     * @author mark
     */
	public function actionErrorOperation()
	{
	    $aErrorOrderId = isset($_POST['checkboxes']) ? $_POST['checkboxes'] : array();
	    $aLocation  = array( 0 => array( 'text' => '返回: 异常账变处理', 'href' => url( 'account', 'checkorder' ) ) );
	    if ( empty($aErrorOrderId))
	    {
	        sysMessage( "请选择需要处理的异常账变", 1, $aLocation );
	    }
	    $bSucc = TRUE;
	    /* @var $oDomain model_domains */
	    $oDomain     = A::singleton('model_domains');
	    $aResult     = $oDomain->domainGetOne( 'domain', " `status`='1' LIMIT 1 " );
	    if( empty($aResult) )
	    {
	        die('error');
	    }
	    $oOrder = new model_orders();
	    $sErrorOrderId = implode( ',', $aErrorOrderId );
	    $aErrorOrderList = $oOrder->getOrderList( "*", " `entry` in( $sErrorOrderId ) ", FALSE );
	    foreach ($aErrorOrderList as $aErrorOrderId )
	    {
	        // step01 初始化数据, 并启用 "转账调度器"  model_transferdispatcher()
	        $aTranfer['iAdminId']        = $_SESSION['admin'];
	        $aTranfer['sAdminName']      = $_SESSION['adminname'];
	        $aTranfer['iUserId']         = $aErrorOrderId['fromuserid'];
	        $aTranfer['iOrderId']        = intval( $aErrorOrderId['entry'] );
	        $aTranfer['iFromChannelId']  = intval( SYS_CHANNELID );
	        $aTranfer['iToChannelId']    = intval( $aErrorOrderId['transferchannelid'] );
	        $aTranfer['fMoney']          = floatval( $aErrorOrderId['amount'] );
	        $aTranfer['sMethod']         = 'USER_TRAN'; // 用户转账
	        $aTranfer['sUniqueKey']      = $aErrorOrderId['uniquekey'];
	        // ------------- 临时代码开始 ---------------------------------------------
            // TODO _a高频、低频并行前期临时程序
            // 增加固定频道ID:  [toChannelId] => 99  表示转至高频,不同的处理方式
            /*if( $aTranfer['iToChannelId'] == 99 )
            { // 如果 [toChannelId] = 99, 则使用并行期间临时的转账异常处理调度器
                $aTranfer['bIsTemp'] = 1;
                $oChannelApi = new channelapi( 0, 'channelTransitionErrorDispatcherTmp', TRUE );
                $oChannelApi->setResultType('serial');
                $oChannelApi->setBaseDomain( $aResult['domain'] );
                $oChannelApi->setTimeOut(15);
                $oChannelApi->sendRequest( $aTranfer );  // 发送给转账异常调度器
                $mAnswers    = $oChannelApi->getDatas();    // 获取转账 API 返回的结果
                if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
                {
                   $bSucc = FALSE;
                }
            }
            else
            {*/
                // ------------- 临时代码结束 ---------------------------------------------
                // step02 调用 PASSPORT平台 API.转账异常处理调度器
                $oChannelApi = new channelapi( 0, 'channelTransitionErrorDispatcher', TRUE );
                $oChannelApi->setResultType('serial');
                $oChannelApi->setBaseDomain( $aResult['domain'] );
                $oChannelApi->setTimeOut(15);
                $oChannelApi->sendRequest( $aTranfer );  // 发送转账请求给错误处理调度器
                $mAnswers = $oChannelApi->getDatas();
                if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
                {
                    $bSucc = FALSE;
                }
//            }
	    }
	    if( $bSucc )
	    {
	        sysMessage( "您的账变异常处理已成功", 0, $aLocation );
	    }
	    else 
	    {
	        sysMessage( "抱歉, 转账异常处理没有完成,重新再试一次", 1, $aLocation );
	    }
	    exit;
	}
	
	
	/**
	 * 开户失败检查
	 * URL:./index.php?controller=account&action=checkuser
     * @author mark
	 */
	/*public function actionCheckUser()
	{
	    $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 10;
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;
	    $oUser = new model_user();
        $aErrorUser = $oUser->getErrorUser( $pn, $p, " `status` = '0' " );
        $oPager = new pages( $aErrorUser['affects'], $pn, 10 );
	    $GLOBALS["oView"]->assign( "ur_here",      "开户失败检查" );
	    $GLOBALS['oView']->assign( "aErrorUser",  $aErrorUser['results'] );
	    $GLOBALS['oView']->assign( "pageinfo",     $oPager->show() );
        $GLOBALS['oView']->assign( 'sSysTopMessage', $aErrorUser['affects']);
        $iAutoReflushSec = 60;
        $GLOBALS['oView']->assign( 'sSysAutoReflushSec', $iAutoReflushSec);
        $GLOBALS['oView']->assign( 'sSysMetaMessage', 
	    				'<META http-equiv="REFRESH" CONTENT="'.$iAutoReflushSec.'" />'); // 自动刷新 for 财务
        $oUser->assignSysInfo();
	    $GLOBALS['oView']->display("account_checkuser.html");
	}*/
	
	/**
	 * 开户失败处理
	 * URL:./index.php?controller=account&action=erroruseroperation
     * @author mark
	 */
	/*public function actionErrorUserOperation()
	{
	    $aLocation  = array( 0 => array( 'text' => '返回: 开户失败用户检查', 'href' => url( 'account', 'checkuser' ) ) );
	    $aErrorUserId = isset($_POST['checkboxes']) ? $_POST['checkboxes'] : array();
	    if ( empty($aErrorUserId))
	    {
	        sysMessage( "请选择需要处理的用户", 1, $aLocation );
	    }
	    $bSucc = TRUE;
	    $bFlag = TRUE;
	    $aErrorType = array();
	    $oUser = new model_user();
	    foreach ( $aErrorUserId as $iUserId )
	    {
	        $aUserInfo = $oUser->getErrorUser( 0, 0, " u.`userid` = '" . $iUserId . "'" );
	        $aUserSyncData = array(
	                       'iUserId'       => $aUserInfo['userid'],
	                       'sUserName'     => $aUserInfo['username'],
	                       'sNickName'     => urlencode($aUserInfo['nickname']),
	                       'sPassword'     => $aUserInfo['loginpwd'],
	                       'iParentId'     => $aUserInfo['parentid'],
	                       'sFundPassword' => $aUserInfo['securitypwd'],
	                       'iUserType'     => $aUserInfo['usertype'],
	                       'iBLimit'       => 0
	        );
	        $oChannelApi = new channelapi( 99, 'interfaceuser', TRUE );
	        $oChannelApi->setResultType('serial');
	        $oChannelApi->setTimeOut(15);
	        $oChannelApi->sendRequest( $aUserSyncData );
	        $mAnswers = $oChannelApi->getDatas();
	        if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
	        {
	            $bSucc = FALSE;
	            $bFlag = $oUser->updateErrorUser( $iUserId, 'changeoperatimes');
	            $aErrorType[] = $mAnswers['data'];
	        }
	        else
	        {
	            $bFlag = $oUser->updateErrorUser( $iUserId, 'changestaus');
	        }
	    }
	    if( $bSucc && $bFlag )
	    {
	        sysMessage( "您的开户失败处理已成功", 0, $aLocation );
	    }
	    elseif( in_array('1956', $aErrorType) )
	    {
	        sysMessage( "抱歉, 开户失败处理没有完成,重新再试一次:数据冲突,ID可能被占用！", 1, $aLocation );
	    }
	    else 
	    {
	        sysMessage( "抱歉, 开户失败处理没有完成,重新再试一次", 1, $aLocation );
	    }
	    exit;
	}*/
	
	
	
	/**
	 * 查询重复充值的记录
	 * 
	 * @version 	v1.0	2010-06-05
	 * @author 		louis
	 *
	 */
	public function actionRepeatLoad(){
		$oRepeat = new model_pay_repeatload();
		$oOrder = new model_orders();
		$oRepeat->StatusList = 1; // 充值成功
		$oRepeat->OrderTypeList = ORDER_TYPE_ZXCZ; // 在线充值
		$oRepeat->StartTime = $sStarttime 	= isset($_GET['starttime']) ? $_GET['starttime'] : date("Y-m-d 02:20:00",strtotime("-1 days"));
        $oRepeat->EndTime = $sEndtime		= isset($_GET['endtime']) ? $_GET['endtime'] : date("Y-m-d 02:20:00",time());
		
		$aResult = $oRepeat->getRecord();
        $GLOBALS["oView"]->assign( "ur_here",      "重复充值列表" );
        $GLOBALS["oView"]->assign( "resultlist",      $aResult );
        $GLOBALS["oView"]->assign( "starttime",      $sStarttime );
        $GLOBALS["oView"]->assign( "endtime",      $sEndtime );
        $oRepeat->assignSysInfo();
        $GLOBALS['oView']->display("account_repeatload.html");
        EXIT;
	}
	
	
	
	
	/**
	 * 同步奖金组失败处理
	 *
	 * @version 	v1.0	2010-08-24
	 * @author 		louis
	 * 
	 */
	public function actionErrSyncPrize(){
		$aLocation  = array( 0 => array( 'text' => '返回: 开户同步失败处理', 'href' => url( 'account', 'errsyncprize' ) ) );
		if ( $_POST['flag'] == 'deal' ){
			// 数据检查
			if ( empty($_POST['errors']) ){
				sysMessage( "数据提交错误，请检查后重新提交！", 1, $aLocation );
			}
			$oErrorDeal = new model_errordeal();
			$sErrorId = "";
			foreach ($_POST['errors'] as $k => $v){
				$oErrorDeal->Id = intval($v);
				$aResult = $oErrorDeal->getOne();
				if ( empty($aResult) ) {
					$sErrorId .= $v . ',';
					continue;
				}
				// 如果记录存在，检查上下级用户是否被删除
				$oErrorDeal->parentId = $aResult['parent_id'];
				$oErrorDeal->childId  = $aResult['child_id'];
				$aDeal = $oErrorDeal->checkUser();
				if ( $aDeal['stauts'] === 0 ){ // 失败
					$sErrorId .= $v . ',';
					// 写入操作管理员信息
					$oErrorDeal->adminId = $_SESSION['admin'];
					$oErrorDeal->adminName = $_SESSION['adminname'];
					$oErrorDeal->setAdmin();
					continue;
				}
				if ( $aDeal['delete'] === 1 ){ // 用户被删除，将记录直接设置为已处理
					$oErrorDeal->status = 2;
					$bResult = $oErrorDeal->setError();
					
					// 写入操作管理员信息
					$oErrorDeal->adminId = $_SESSION['admin'];
					$oErrorDeal->adminName = $_SESSION['adminname'];
					$bAdmin = $oErrorDeal->setAdmin();
					
					if ( $bResult === false ){
						$sErrorId .= $v . ',';
					}
					continue;
				}
				// 真正的处理操作
				$mDeal = $oErrorDeal->dealError();
				if ( $mDeal === -2 ){ // 上级用户手动执行了操作，直接将记录改为处理成功，不记录操作管理员信息
					$oErrorDeal->status = 2;
					$bResult = $oErrorDeal->setError();
					if ( $bResult === false ){ // 修改日志状态失败
						$sErrorId .= $v . ',';
					}
					continue;
				}
				if ( $mDeal === -1 || $mDeal === -3 || $mDeal === false ){ // 操作失败
					// 写入操作管理员信息
					$oErrorDeal->adminId = $_SESSION['admin'];
					$oErrorDeal->adminName = $_SESSION['adminname'];
					$bAdmin = $oErrorDeal->setAdmin();
					$sErrorId .= $v . ',';
					continue;
				}
				if ( $mDeal === true ){ // 操作成功，记录操作管理员信息，修改状态
					// 写入操作管理员信息
					$oErrorDeal->adminId = $_SESSION['admin'];
					$oErrorDeal->adminName = $_SESSION['adminname'];
					$bAdmin = $oErrorDeal->setAdmin();
					if ( $bAdmin === true ){
						$oErrorDeal->status = 2;
						$bResult = $oErrorDeal->setError();
						if ( $bResult === false ){
							$sErrorId .= $v . ',';
							continue;
						}
					}
				}
			}
			
			if ( !empty($sErrorId) ){
				$sErrorId = substr( $sErrorId, 0, -1 );
				sysMessage( "抱歉, 编号为 {$sErrorId} 的记录操作失败", 1, $aLocation );
			} else {
				sysMessage( "操作成功", 0, $aLocation );
			}
		}
		$oErrorDeal = new model_errordeal();
		$aError = $oErrorDeal->getErrorSyncPrizeList();
		$GLOBALS["oView"]->assign( "ur_here",      "同步开户失败处理" );
		$GLOBALS["oView"]->assign( "aError",       $aError );
        $oErrorDeal->assignSysInfo();
        $GLOBALS['oView']->display("account_errordeal.html");
        EXIT;
	}
}
?>