<?php
/**
 * 文件 : /_app/controller/data.php
 * 功能 : 控制器 - 数据清理
 * 
 * 功能:
 * - actionResetzero()        负余额清零
 * - actiondoresetzero()      执行负余额清零
 * - actionChecksnapshot()    检查快照报表
 * - actionLogclear()         日志清理设置
 * - actionIssueClear()       奖期清理设置
 * - actionProjectclear()     方案清理设置
 * - actionOrderclear()       帐变清理设置
 * - actionCheckOrder()       检查账变异常
 * - actionErrorOperation()   处理异常账变
 * - actionCheckIssueError()  开奖异常列表
 * 
 * @author     TOM,SAUL,MARK
 * @version    1.2.0
 * @package    lowadmin
 */

class controller_data extends basecontroller
{
    /**
     * 负余额清零
     * URL: ./index.php?controller=data&action=resetzero
     * @author SAUL
     * 对负余额帐户通过API填平用户资金
     */
    function actionResetzero()
    {
        /* @var $oUser model_user */
        $oUser      = A::singleton("model_user");
        $aUserlist  = $oUser->getUnderZeroUser();
        $GLOBALS['oView']->assign( "ur_here",    "负余额清零" );
        $GLOBALS['oView']->assign( "userlist",   $aUserlist );
        $oUser->assignSysInfo();
        $GLOBALS['oView']->display("data_resetzero.html");
        EXIT;
    }


    /**
     * 负余额清零(执行)
     * URL = ./index.php?controller=data&action=doresetzero
     * @author: Tom
     * 注意: 此功能只清理 '账户余额' !!!!
     * 
     * 对提交数据(用户ID) 进行负余额清零操作
     * 例: 低频游戏 -1700,  银行平台: 800
     *     对银行平台扣除低频的负余额数 1700, 转入低频, 使低频金额为0
     */
    function actionDoresetzero()
    {
        $aLocation[0] = array('text'=>'负余额清零','href'=>url('data','resetzero'));
        if( !isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']) )
        {
            sysMessage("未选择数据", 1);
        }
        // 1, 数据整理. 根据POST传递的用户数组, 查询用户负余额数量
        $oUser    = A::singleton('model_user');
        $aUserArr = $oUser->getUnderZeroUser( $_POST['checkboxes'] );
        unset($oUser);
        if( empty($aUserArr) )
        {
            sysMessage("处理的用户ID中, 未发现负资金的情况", 1, $aLocation );
        }
        // 2, 获取可用域名, 初始化 API 调用域名 
        $oDomain     = new model_domains();
        $aResult     = $oDomain->domainGetOne( 'domain', " `status`='1' LIMIT 1 " );
        if( empty($aResult) )
        {
            sysMessage("操作失败", 1, $aLocation );
        }
        // 3, 初始化转账调度器
        $oChannelApi = new channelapi( 0, 'channelTransitionDispatcher', TRUE );
        $oChannelApi->setBaseDomain( $aResult['domain'] );        
        // 4, 循环调用 PassPort 转账 API. 每次调用结果输出并刷新浏览器缓存
        foreach ( $aUserArr as $v )
        {
            // 转账数据整理
            $aTranfer['sMethod']         = 'SYS_ZERO';     // 负余额清零
            $aTranfer['iAdminId']        = $_SESSION['admin'];
            $aTranfer['sAdminName']      = $_SESSION['adminname'];
            $aTranfer['iUserId']         = intval( $v['userid'] );
            $aTranfer['iFromChannelId']  = 0;              // 银行平台默认 0
            $aTranfer['iToChannelId']    = SYS_CHANNELID;  // 转入本平台
            $aTranfer['fMoney']          = floatval( abs($v['channelbalance']) );
            $oChannelApi->sendRequest( $aTranfer );        // 发送转账请求给调度器
            $mAnswers = $oChannelApi->getDatas();          // 获取转账 API 返回的结果
            if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
            {
                echo "失败 uid=".$v['userid']." fMoney=".$v['channelbalance']." <br/> ";
            }
            else 
            {
                echo "成功 uid=".$v['userid']." fMoney=".$v['channelbalance']." <br/> ";
            }
            @ob_flush();
            flush();
        }
        EXIT;
    }


    /**
     * 检查快照状态
     * URL = ./index.php?controller=data&action=checksnapshot
     * @author Tom
     * 可查询所有快照列表, 并根据系统差帐金额设置. 显示是否超过报警范围
     */
    function actionChecksnapshot()
    {
        // 01, 整理搜索条件
        $aSearch['dates'] = isset($_GET['dates']) ? $_GET['dates'] : -1;
        $aHtmlValue       = array();
        $aResult          = array();
        /* @var $oBankSnapshot model_snapshot */
        $oBankSnapshot    = A::singleton("model_snapshot");
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
        $GLOBALS['oView']->display("data_checksnapshot.html");
        EXIT;
    }



    /**
     * 日志清理设置
     * URL: ./index.php?controller=data&actio=logclear
     * @author SAUL
     * 说明:对日志进行清理做相关的计划任务
     */
    function actionLogclear()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $iDay       = (isset($_POST["day"])&&is_numeric($_POST["day"]))? intval($_POST["day"]) :5;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"]) ? $_POST["endtime"] : "03:00:00";
            $iIsRun     = (isset($_POST["run"])&&is_numeric($_POST["run"]))? intval($_POST["run"]):0;
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig    = array(
                "logcleardate"      => $iDay,
                "logclearstarttime" => $sStartTime,
                "logclearendtime"   => $sEndTime,
                "logclearrun"       => $iIsRun
            );
            $aLocation[0] = array( 'text'=>'日志清理', 'href'=>url('data','logclear') );
            if ($oConfig->updateConfigs($aConfig))
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
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig = $oConfig->getConfigs(
                    array('logcleardate','logclearstarttime','logclearendtime','logclearrun')
                );
            $GLOBALS["oView"]->assign( "day",       isset($aConfig["logcleardate"])?$aConfig["logcleardate"]:"" );
            $GLOBALS["oView"]->assign( "starttime", isset($aConfig["logclearstarttime"])?$aConfig["logclearstarttime"]:"" );
            $GLOBALS["oView"]->assign( "endtime",   isset($aConfig["logclearendtime"])?$aConfig["logclearendtime"]:"" );
            if( isset($aConfig["logclearrun"]) )
            {
                $GLOBALS['oView']->assign("run",   $aConfig["logclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "日志清理");
            $GLOBALS['oView']->assign( "action",    "logclear");
            $GLOBALS["oView"]->display( "data_logclear.html");
            EXIT;
        }
    }


    
	/**
     * 总代投注量表清理(设置)
     * URL: ./index.php?controller=data&actio=recentbuyclear
     * 6/23/2010
     */
    function actionRecentbuyClear()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $iDay       = (isset($_POST["day"])&&is_numeric($_POST["day"]))? intval($_POST["day"]) :5;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"]) ? $_POST["endtime"] : "03:00:00";
            $iIsRun     = (isset($_POST["run"])&&is_numeric($_POST["run"]))? intval($_POST["run"]):0;
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig    = array(
                "recentbuycleardate"      => $iDay,
                "recentbuyclearstarttime" => $sStartTime,
                "recentbuyclearendtime"   => $sEndTime,
                "recentbuyclearrun"       => $iIsRun
            );
            $aLocation[0] = array( 'text'=>'总代投注量表清理(设置)', 'href'=>url('data','recentbuyclear') );
            if ($oConfig->updateConfigs($aConfig))
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
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig = $oConfig->getConfigs(
                    array('recentbuycleardate','recentbuyclearstarttime','recentbuyclearendtime','recentbuyclearrun')
                );
            $GLOBALS["oView"]->assign( "day",       isset($aConfig["recentbuycleardate"])?$aConfig["recentbuycleardate"]:"" );
            $GLOBALS["oView"]->assign( "starttime", isset($aConfig["recentbuyclearstarttime"])?$aConfig["recentbuyclearstarttime"]:"" );
            $GLOBALS["oView"]->assign( "endtime",   isset($aConfig["recentbuyclearendtime"])?$aConfig["recentbuyclearendtime"]:"" );
            if( isset($aConfig["recentbuyclearrun"]) )
            {
                $GLOBALS['oView']->assign("run",   $aConfig["recentbuyclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "总代投注量表清理(设置)");
            $GLOBALS['oView']->assign( "action",    "recentbuyclear");
            $GLOBALS["oView"]->display( "data_logclear.html");
            exit;
        }
    }

    
	/**
     * 数据快照表清理(设置)
     * URL: ./index.php?controller=data&actio=snapshotclear
     * 6/23/2010
     */
    function actionSnapshotClear()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $iDay       = (isset($_POST["day"])&&is_numeric($_POST["day"]))? intval($_POST["day"]) :5;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"]) ? $_POST["endtime"] : "03:00:00";
            $iIsRun     = (isset($_POST["run"])&&is_numeric($_POST["run"]))? intval($_POST["run"]):0;
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig    = array(
                "snapshotcleardate"      => $iDay,
                "snapshotclearstarttime" => $sStartTime,
                "snapshotclearendtime"   => $sEndTime,
                "snapshotclearrun"       => $iIsRun
            );
            $aLocation[0] = array( 'text'=>'数据快照表清理(设置)', 'href'=>url('data','snapshotclear') );
            if ($oConfig->updateConfigs($aConfig))
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
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig = $oConfig->getConfigs(
                    array('snapshotcleardate','snapshotclearstarttime','snapshotclearendtime','snapshotclearrun')
                );
            $GLOBALS["oView"]->assign( "day",       isset($aConfig["snapshotcleardate"])?$aConfig["snapshotcleardate"]:"" );
            $GLOBALS["oView"]->assign( "starttime", isset($aConfig["snapshotclearstarttime"])?$aConfig["snapshotclearstarttime"]:"" );
            $GLOBALS["oView"]->assign( "endtime",   isset($aConfig["snapshotclearendtime"])?$aConfig["snapshotclearendtime"]:"" );
            if( isset($aConfig["snapshotclearrun"]) )
            {
                $GLOBALS['oView']->assign("run",   $aConfig["snapshotclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "数据快照表清理(设置)");
            $GLOBALS['oView']->assign( "action",    "snapshotclear");
            $GLOBALS["oView"]->display( "data_logclear.html");
            exit;
        }
    }
    
    
    
    /**
     * 奖期清理设置
     * URL: ./index.php?controller=data&action=issueclear 
     * @author SAUL
     * 说明:对奖期进行清理(注意追号追20期)
     */
    function actionIssueClear()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $iDay       = (isset($_POST["day"])&&is_numeric($_POST["day"]))? intval($_POST["day"]) :5;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"])?$_POST["endtime"] : "03:00:00";
            $iIsRun     = (isset($_POST["run"])&&is_numeric($_POST["run"]))? intval($_POST["run"]):0;
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig    = array(
                "issuecleardate"      => $iDay,
                "issueclearstarttime" => $sStartTime,
                "issueclearendtime"   => $sEndTime,
                "issueclearrun"       => $iIsRun
            );
            $aLocation[0] = array( 'text'=>'奖期清理','href'=>url('data','issueclear') );
            if( $oConfig->updateConfigs($aConfig) )
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
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig = $oConfig->getConfigs(
                    array('issuecleardate','issueclearstarttime','issueclearendtime','issueclearrun')
                );
            $GLOBALS["oView"]->assign( "day",       isset($aConfig["issuecleardate"])?$aConfig["issuecleardate"] : "" );
            $GLOBALS["oView"]->assign( "starttime", isset($aConfig["issueclearstarttime"])?$aConfig["issueclearstarttime"]:"" );
            $GLOBALS["oView"]->assign( "endtime",   isset($aConfig["issueclearendtime"])?$aConfig["issueclearendtime"]:"" );
            if( isset($aConfig["issueclearrun"]) )
            {
                $GLOBALS['oView']->assign( "run",   $aConfig["issueclearrun"] );
            }
            $GLOBALS["oView"]->assign( "ur_here",   "奖期清理" );
            $GLOBALS['oView']->assign( "action",    "issueclear" );
            $GLOBALS["oView"]->display("data_logclear.html");
            EXIT;
        }
    }



    /**
     * 方案清理设置
     * URL: ./index.php?controller=data&action=projectclear
     * @author SAUL
     * 对方案进行备份清理(20期)
     */
    function actionProjectclear()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $iDay       = (isset($_POST["day"])&&is_numeric($_POST["day"]))? intval($_POST["day"]) :5;
            $sStartTime = isset($_POST["starttime"])?daddslashes($_POST["starttime"]):"02:00:00";
            $sEndTime   = isset($_POST["endtime"])?$_POST["endtime"]:"03:00:00";
            $iIsRun     = (isset($_POST["run"])&&is_numeric($_POST["run"]))? intval($_POST["run"]):0;
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig    = array(
                "projectcleardate"      => $iDay,
                "projectclearstarttime" => $sStartTime,
                "projectclearendtime"   => $sEndTime,
                "projectclearrun"       => $iIsRun
            );
            $aLocation[0] = array( 'text'=>'方案清理','href'=>url('data','projectclear') );
            if ( $oConfig->updateConfigs($aConfig) )
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
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig = $oConfig->getConfigs(
                    array('projectcleardate','projectclearstarttime','projectclearendtime','projectclearrun')
                );
            $GLOBALS["oView"]->assign( "day",       isset($aConfig["projectcleardate"])?$aConfig["projectcleardate"]:"" );
            $GLOBALS["oView"]->assign( "starttime", isset($aConfig["projectclearstarttime"])?$aConfig["projectclearstarttime"]:"" );
            $GLOBALS["oView"]->assign( "endtime",   isset($aConfig["projectclearendtime"])?$aConfig["projectclearendtime"]:"" );
            if( isset($aConfig["projectclearrun"]) )
            {
                $GLOBALS['oView']->assign("run",   $aConfig["projectclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "方案清理" );
            $GLOBALS['oView']->assign( "action",    "projectclear" );
            $GLOBALS["oView"]->display( "data_logclear.html" );
            EXIT;
        }
    }



    /**
     * 帐变清理设置
     * URL:./index.php?controller=data&action=orderclear
     * @author SAUL
     * 说明:对帐变进行清理设置
     */
    function actionOrderClear()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $iDay       = (isset($_POST["day"])&&is_numeric($_POST["day"])) ? intval($_POST["day"]) : 5;
            $sStartTime = getFilterDate($_POST["starttime"],"H:i:s") ? getFilterDate($_POST["starttime"],"H:i:s") : "02:00:00";
            $sEndTime   = getFilterDate($_POST["endtime"],"H:i:s")   ? getFilterDate($_POST["endtime"],"H:i:s")   : "03:00:00";
            $iIsRun     = (isset($_POST["run"])&&is_numeric($_POST["run"])) ? intval($_POST["run"]) : 0;
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig    = array(
                "orderscleardate"      => $iDay,
                "ordersclearstarttime" => $sStartTime,
                "ordersclearendtime"   => $sEndTime,
                "ordersclearrun"       => $iIsRun
            );
            $aLocation[0]  = array('text'=>'帐变清理','href'=>url('data','orderclear'));
            if( $oConfig->updateConfigs($aConfig) )
            {
                sysMessage( '操作成功', 0, $aLocation );
            }
            else
            {
                sysMessage( '操作失败', 1, $aLocation );
            }
        }
        else
        {
            /* @var $oConfig model_config */
            $oConfig    = A::singleton("model_config");
            $aConfig = $oConfig->getConfigs(
                        array('orderscleardate','ordersclearstarttime',
                              'ordersclearendtime','ordersclearrun')
                    );
            $GLOBALS["oView"]->assign("day",       isset($aConfig["orderscleardate"])?$aConfig["orderscleardate"]:"");
            $GLOBALS["oView"]->assign("starttime", isset($aConfig["ordersclearstarttime"])?$aConfig["ordersclearstarttime"]:"");
            $GLOBALS["oView"]->assign("endtime",   isset($aConfig["ordersclearendtime"])?$aConfig["ordersclearendtime"]:"");
            if( isset($aConfig["ordersclearrun"]) )
            {
                $GLOBALS['oView']->assign("run",   $aConfig["ordersclearrun"]);
            }
            $GLOBALS["oView"]->assign( "ur_here",   "帐变清理" );
            $GLOBALS['oView']->assign( "action",    "orderclear" );
            $GLOBALS["oView"]->display( "data_logclear.html" );
            EXIT;
        }
    }



    /**
     * 检查账变异常
     * URL:./index.php?controller=data&action=checkorder
     * @author mark
     */
    function actionCheckOrder()
    {
        $sUserName      = isset($_GET['username']) ? daddslashes($_GET['username']) : '';
        $iOrderTypeId   = isset($_GET['ordertypeid']) && is_numeric($_GET['ordertypeid']) ? intval($_GET['ordertypeid']) : 0;
        $sStarttime     = isset($_GET['starttime']) ? $_GET['starttime'] : date("Y-m-d H:i:s",strtotime("-1 days"));
        $sEndtime       = isset($_GET['endtime']) ? $_GET['endtime'] : date("Y-m-d H:i:s",time());
        $pn             = isset($_GET['pn'])? intval($_GET['pn']) : 25;
        $p              = isset($_GET['p'])  ? intval($_GET['p'])  : 0;
        $searchpn = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25;
        if( !empty($sUserName) )
        {
            /* @var $oUser model_user */
            $oUser   = A::singleton("model_user" , $GLOBALS['aSysDbServer']['report']);
            $iUserId = $oUser->getUseridByUsername( $sUserName );
        }
        else
        {
            $iUserId        = 0;
        }
        $aCondition = array( 
                                "userid" => $iUserId,
                                "ordertypeid" => $iOrderTypeId,
                                "starttime" => $sStarttime,
                                "endtime"=>$sEndtime
                            );
        /* @var $oOrder model_orders*/
        $oOrder = A::singleton("model_orders", $GLOBALS['aSysDbServer']['report']);
        $aErrorOrder = $oOrder->getErrorOrder( $aCondition, $searchpn, $p );
        $aOrderType = array(
                                ORDER_TYPE_ZRPD     => "转入频道(银行=>低频)",
                                ORDER_TYPE_PDZC     => "频道转出(低频=>银行)",
                                ORDER_TYPE_PDXEZC   => "频道小额转出"
                            );
        $oPager = new pages( $aErrorOrder['affects'], $searchpn, 10 );
        $GLOBALS["oView"]->assign( "ur_here",     "转账失败检查" );
        $GLOBALS['oView']->assign( "searchpn",    $searchpn );
        $GLOBALS['oView']->assign( "aErrorOrder", $aErrorOrder['results'] );
        $GLOBALS['oView']->assign( "starttime",   $sStarttime );
        $GLOBALS['oView']->assign( "endtime",     $sEndtime );
        $GLOBALS['oView']->assign( "ordertypeid", $iOrderTypeId );
        $GLOBALS['oView']->assign( "username",    $sUserName );
        $GLOBALS['oView']->assign( "aOrderType",  $aOrderType );
        $GLOBALS['oView']->assign( "pageinfo",    $oPager->show() );
        $GLOBALS['oView']->assign( 'sSysTopMessage', $aErrorOrder['affects']);
        $iAutoReflushSec = 60;
        $GLOBALS['oView']->assign( 'sSysAutoReflushSec', $iAutoReflushSec);
        $GLOBALS['oView']->assign( 'sSysMetaMessage', 
	    				'<META http-equiv="REFRESH" CONTENT="'.$iAutoReflushSec.'" />'); // 自动刷新 for 财务
        $GLOBALS['oView']->assign( 'actionlink',  array( 'href'=>url("data","checkorder"), 'text'=>'清空过滤条件' ) );
        $oOrder->assignSysInfo();
        $GLOBALS['oView']->display("data_checkorder.html");
        EXIT;
    }
    
    
    
     /**
     * 处理账变异常
     * URL:./index.php?controller=data&action=erroroperation
     * @author mark
     */
    public function actionErrorOperation()
	{
	    $aErrorOrderId = isset($_POST['checkboxes']) ? $_POST['checkboxes'] : array();
	    $aLocation  = array( 0 => array( 'text' => '返回: 异常账变处理', 'href' => url( 'data', 'checkorder' ) ) );
	    if ( empty($aErrorOrderId))
	    {
	        sysMessage( "请选择需要处理的账变", 1, $aLocation );
	    }
	    $bSucc = TRUE;
	    /* @var $oDomain model_domains */
	    $oDomain     = A::singleton('model_domains');
	    $aResult     = $oDomain->domainGetOne( 'domain', " `status`='1' LIMIT 1 " );
	    if( empty($aResult) )
	    {
	        die('error');
	    }
	    $oChannelApi = new channelapi( 0, 'channelTransitionErrorDispatcher', TRUE );
	    $oChannelApi->setResultType('serial');
	    $oChannelApi->setBaseDomain( $aResult['domain'] );
	    $oChannelApi->setTimeOut(15);
	    $oOrder = new model_orders();
	    $sErrorOrderId = implode( ',', $aErrorOrderId );
	    $aErrorOrderList = $oOrder->getOrderList( "*", " `entry` in( $sErrorOrderId )", FALSE );
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
	       // step02 调用 PASSPORT平台 API.转账异常处理调度器
	        $oChannelApi->sendRequest( $aTranfer );  // 发送转账请求给异常处理调度器
	        $mAnswers = $oChannelApi->getDatas();
	        if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
	        {
	            $bSucc = FALSE;
	        }
	    }
	    if( $bSucc )
	    {
	        sysMessage( "您的账变异常处理已成功", 0, $aLocation );
	    }
	    else 
	    {
	        sysMessage( "抱歉, 转账异常处理没有完成,重新再试一次", 1,$aLocation );
	    }
	    exit;
	}



	/**
     * 开奖异常列表
     * URL:./index.php?controller=data&action=checkIssueError
     * @author Tom 2009-11-16 15:51
     */
    public function actionCheckIssueError()
    {
        $pn         = isset($_GET['pn'])? intval($_GET['pn']) : 50;
        $p          = isset($_GET['p'])  ? intval($_GET['p'])  : 0;
        $oIssueInfo = new model_issueerror();
        $aResult    = $oIssueInfo->issueExceptionList( $pn, $p );
        //print_rr($aResult);exit;
        $oPager = new pages( $aResult['affects'], $pn );
        $GLOBALS["oView"]->assign( "ur_here",     "开奖异常列表" );
        $GLOBALS['oView']->assign( "aErrorOrder", $aResult['results'] );
        $GLOBALS['oView']->assign( "pageinfo",    $oPager->show() );
        $GLOBALS['oView']->assign( 'actionlink',  array( 'href'=>url("data","checkissueerror"), 'text'=>'清空过滤条件' ) );
        $oIssueInfo->assignSysInfo();
        $GLOBALS['oView']->display("data_checkissueerror.html");
        EXIT;
    }
    
    
    /**
     * 开奖异常详情
     * 
     * @author mark 2009-11-17 11:13
     *
     */
    public function actionCheckIssueErrorInfo()
    {
        $iEntry = isset($_GET['entry']) ? intval($_GET['entry']) : 0;
        $oIssueInfo = new model_issueerror();
        $mResult    = $oIssueInfo->issueExceptionList( 0, 0, $iEntry, FALSE );
        if( $mResult == -1 )
        {
            sysMessage( "请指定异常奖期编号", 1 );
        }
        if( empty($mResult) )
        {
            sysMessage( "没有异常的详情数据", 2 );
        }
        $GLOBALS["oView"]->assign( "ur_here",     "开奖异常详情" );
        $GLOBALS['oView']->assign( "aResult", $mResult );
        $oIssueInfo->assignSysInfo();
        $GLOBALS['oView']->display( "data_checkissueerrorinfo.html" );
    }
}
?>