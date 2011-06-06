<?php
/**
 * 文件 : /_app/controller/game.php
 * 功能 : 控制器 - 游戏平台玩游戏投注等相关操作
 *  
 *  - actionPlay()          参与游戏(用户购彩)
 *  - actionSpecial()       靓号区(靓号区购买)
 *  - actionCodeDynamic()   查看动态调价当前价格
 *  - actionBonusCode()     查看历史中奖号码
 *  - checkIssue()          检测输入的奖期号是否符合规则
 * 
 * @author     james    090914
 * @version    1.2.0
 * @package    lowgame   
 */

class controller_game extends basecontroller
{
	/**
	 * 用户投注
	 */
	function actionPlay()
	{
		error_reporting(0);
		$iUserId = intval($_SESSION['userid']);
		if( empty($_POST['flag']) || ( $_POST['flag']!= 'save' && $_POST['flag']!= 'read' ) )
		{
			$aLocation[0] = array( "title"=>'系统公告',"url"=>url('default', 'start') );
	        if( empty($_REQUEST['pid']) || !is_numeric($_REQUEST['pid']) || intval($_REQUEST['pid']) <= 0 )
	        {//主菜单ID
	            sysMsg( "操作错误", 2 );
	        }
	        $iTopMenuId     = intval($_REQUEST['pid']);
	        $iCurrentMenuId = 0;//默认当前菜单为0
		    if( !empty($_REQUEST['curmid']) && is_numeric($_REQUEST['curmid']) && intval($_REQUEST['curmid']) > 0 )
            {//当前菜单ID
                $iCurrentMenuId = intval($_REQUEST['curmid']);
            }
            if( $iCurrentMenuId == $iTopMenuId )
            {//如果和主菜单ID一样则为0
            	$iCurrentMenuId = 0;
            }
	        
	        //02：获取标签
	        $oUserMenu  = new model_usermenu();
	        $sFields    = "`menuid`,`title`,`description`,`lotteryid`,`methodid`,`controller`,`actioner`";
	        $sCondition = " AND `islabel`='1' AND `isdisabled`='0' AND `menutype`='0' ";
	        $aLabel     = $oUserMenu->userMenuChild( $iTopMenuId, FALSE, $sFields, $sCondition );
	        if( empty($aLabel) )
	        {//没有具体玩法获取已关闭
	        	sysMsg( "游戏已关闭", 2, $aLocation );
	        }
	        //检测标签是否使用
	        $aTempMethodId = array();
	        $aTempArr      = array();
	        foreach( $aLabel as $v )
	        {
	        	$aTempMethodId[]          = $v['methodid'];
	        	$aTempArr[$v['methodid']] = $v;
	        }
	        $aLabel = $aTempArr;
	        //03: 获取相关玩法的信息
	        $oMethod     = new model_method();
	        $sFields     = "`level`,`methodid`,`pid`,`isprizedynamic`,`areatype`,`nocount`";
            $sCondition  = " `isclose`='0' AND `methodid` IN(".implode(",",$aTempMethodId).") ";
	        $aMethodData = $oMethod->methodOneGetList( $sFields, $sCondition );
	        if( empty($aMethodData) )
	        {//玩法已关闭
	        	sysMsg( "游戏已关闭", 2, $aLocation );
	        }
	        $aTempMethodId = array();
	        foreach( $aMethodData as $v )
	        {
	        	$aTempMethodId[]                      = $v['pid'];
	        	$aLabel[$v['methodid']]['methodinfo'] = $v;
	        }
	        foreach( $aLabel as $k=>$v )
	        {//丢弃已关闭的游戏标签
	        	if( !isset($v['methodinfo']) )
	        	{
	        		unset($aLabel[$k]);
	        	}
	        }
	        //04: 根据玩法组获取奖金详情
	        $oUserMethod = new model_usermethodset();
	        $sFields     = "m.`methodid`,upl.`level`,upl.`prize`";
            $sCondition  = " AND m.`methodid` IN (".implode(",",$aTempMethodId).") ";
            $aPrizeData  = $oUserMethod->getUserMethodPrize( $iUserId, $sFields, $sCondition, FALSE );
            if( empty($aPrizeData) )
            {//奖金组获取失败
            	sysMsg( "没有权限", 2, $aLocation );
            }
            $aMethodData = array();
            foreach( $aPrizeData as $v )
            {
            	$aMethodData[$v['methodid']][$v['level']] = $v;
            }
            $aTempArr = array();
            foreach( $aLabel as $k=>&$v )
            {
            	$iTempPid = $v['methodinfo']['pid'];
            	if( isset($aMethodData[$iTempPid]) )
            	{
            		$v['prize'] = $aMethodData[$iTempPid];
            		$aTempArr[] = $v;
            	}
            }
            //05:指定当前菜单
            $aMethodData = array();
            $aLabel      = array();
            foreach( $aTempArr as $v )
            {
            	if( $iCurrentMenuId == $v['menuid'] )
            	{//当前菜单
            		$aMethodData    = $v;
            		
            	}
            	unset($v['methodinfo'],$v['prize']);
            	$aLabel[] = $v;
            }
            if( $iCurrentMenuId == 0 || empty($aMethodData) )
            {
            	$iCurrentMenuId = $aTempArr[0]['menuid'];
            	$aMethodData    = $aTempArr[0];
            }
            unset($aTempArr);
            //06:处理当前期数据
            $aMethodData['methodinfo']['areatype']=unserialize(base64_decode($aMethodData['methodinfo']['areatype']));
            $aMethodData['methodinfo']['nocount'] =unserialize($aMethodData['methodinfo']['nocount']);
            $iTempLevel = 1;
            if( $aMethodData['methodinfo']['level'] > 1 )
            {//多奖级处理
            	$aTempLevel = array();
            	foreach( $aMethodData['methodinfo']['nocount'] as $k=>$v )
            	{
            		if( isset($v['use']) && $v['use'] == 1 )
            		{//使用了该奖级
            			$aTempLevel[] = $k;
            		}
            	}
            	if( count($aTempLevel) > 1 )
            	{//如果使用了多个奖级
            		sort($aTempLevel);
            	}
            	$iTempLevel = $aTempLevel[0];
            	unset($aTempLevel);
            }
            $aMethodData['prize'] = $aMethodData['prize'][$iTempLevel];
            unset($iTempLevel);
            //获取用户余额信息
            $oUserFund = new model_userfund();
            $aUserInfo = $oUserFund->getFundByUser($iUserId, "", SYS_CHANNELID);
            if( empty($aUserInfo) )
            {
            	sysMsg( "你的资金被其他操作锁定", 2 );
            }
            //07:获取当前菜单的当前期数据
            $iNowTime                 = time();     //设置当前时间，避免后面多次调用
            $oIssue                   = new model_issueinfo();
            $aMethodData['issueinfo'] = $oIssue->getCurrentIssue( $aMethodData['lotteryid'] );
            if( empty($aMethodData['issueinfo']) )
            {
            	sysMsg( "未到销售时间", 2, $aLocation );
            }
            if( strtotime($aMethodData['issueinfo']['dynamicprizestart']) < $iNowTime 
                && strtotime($aMethodData['issueinfo']['dynamicprizeend']) > $iNowTime 
                && $aMethodData['methodinfo']['isprizedynamic'] == 0 )
            {//在调价时间范围内[但是不调价的则不允许购买]
            	sysMsg( "当期通选玩法销售已经截止，请于下期购买。通选销售时间为每日05：00-14：00", 2, $aLocation );
            }
            $oConfig = new model_config();
            $iStartTime = strtotime($oConfig->getConfigs("stop_buy_start"));
            $iEndTime = strtotime($oConfig->getConfigs("stop_buy_end"));
            if ($iStartTime > 0 && $iEndTime > 0 && $iEndTime >= $iStartTime){
                $iNow = time();
                if ($iNow >= $iStartTime && $iNow <= $iEndTime){
                    sysMsg( "未到销售时间", 2, $aLocation );
                }
            }
            //08：获取追号的期数
            $aMethodData['taskinfo'] = $oIssue->getTaskIssue( $aMethodData['lotteryid'], 
                                        $aMethodData['issueinfo']['issueid'], "`issue`,`issueid`" );
            //09：获取最近一期的开奖号码
            $aMethodData['lastissue'] = $oIssue->getLastIssueCode( $aMethodData['lotteryid'], "`issue`,`code`" );
            if( empty($aMethodData['lastissue']) )
            {
                $aMethodData['lastissue'] = array( "issue"=>'0000', 'code'=>'000' );
            }
            else 
            {
            	$aMethodData['lastissue'] = $aMethodData['lastissue'][0];
            }
            //10:获取最近10期中奖号码
            $oGameManage = new model_gamemanage();
            $aHistoryCode = $oGameManage->getHistoryBounsCode( $aMethodData['lotteryid'], 10, 1 );
            $aUserInfo['limitbons']         = intval(getConfigValue( 'limitbonus', 100000 ));
            $aMethodData['nowtime']         = date("Y-m-d H:i:s");
            $aUserInfo['bigordercancel']    = intval(getConfigValue( 'bigordercancel', 10000 ));
            $aUserInfo['bigordercancelpre'] = floatval(getConfigValue( 'bigordercancelpre', 0.01 ));
            $GLOBALS['oView']->assign( "pid", $iTopMenuId );//主菜单ID
            $GLOBALS['oView']->assign( "curmid", $iCurrentMenuId );//当前菜单ID
            $GLOBALS['oView']->assign( "labels", $aLabel );//标签
            $GLOBALS['oView']->assign( "methoddata", $aMethodData );//玩法信息
            $GLOBALS['oView']->assign( "userinfo", $aUserInfo );//用户信息
            $GLOBALS['oView']->assign( "historycode", $aHistoryCode );
            $GLOBALS['oView']->assign( "sys_header_title", TRUE );
            $oUserMethod->assignSysInfo();
            $GLOBALS['oView']->display( 'game_play.html' );
            exit;
		}
		elseif( $_POST['flag'] == 'read' )
		{//获取当前期[时间自动过了以后读取下一期数据]
		    if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) || intval($_POST['lotteryid']) <= 0 )
            {//彩种ID
                die("empty");
            }
            $iLotteryId  = intval($_POST['lotteryid']);
            $oIssue      = new model_issueinfo();
            //获取当前期数据
		    $aMethodData = $oIssue->getCurrentIssue( $iLotteryId );
            if( empty($aMethodData) )
            {
                die("empty");
            }
            //获取追号的期数
            $aMethodData['taskinfo'] = $oIssue->getTaskIssue($iLotteryId, 
                                                    $aMethodData['issueid'], "`issue`,`issueid`" );
		    $oUserFund               = new model_userfund();
            $aMethodData['userinfo'] = $oUserFund->getFundByUser($iUserId, "", SYS_CHANNELID);
            if( empty($aMethodData['userinfo']) )
            {
                die("empty");
            }
            $aMethodData['nowtime']  = date("Y-m-d H:i:s");
            echo "[".json_encode($aMethodData)."]";
            exit;
		}
		elseif( $_POST['flag'] == 'save' )
		{
			//01：数据完整性检测
			if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) || $_POST['lotteryid'] <= 0 )
			{//彩种
				die(ajaxMsg("error","操作错误"));
			}
			$iLotteryId = intval($_POST['lotteryid']);
			if( empty($_POST['methodid']) || !is_numeric($_POST['methodid']) || $_POST['methodid'] <= 0 )
            {//玩法
                die(ajaxMsg("error","操作错误"));
            }
            $iMethodId = intval($_POST['methodid']);
            $aData     = array(); //投注数据
            if( !isset($_POST['lottery_confirmnums']) || $_POST['lottery_confirmnums'] == "" )
            {//购买号码
                die(ajaxMsg("error","请选择投注号码"));
            }
            $aData['sNums'] = $_POST['lottery_confirmnums'];
            if( empty($_POST['lottery_currentissue']) )
            {//当期期号
                die(ajaxMsg("error","操作错误"));
            }
            $aData['sIssue'] = $_POST['lottery_currentissue'];
            if( empty($_POST['lottery_totalnum']) || !is_numeric($_POST['lottery_totalnum']) 
                || $_POST['lottery_totalnum'] <= 0 )
            {//购买注数
                die(ajaxMsg("error","操作错误"));
            }
            if( $_POST['lottery_totalnum'] > 1000 )
            {//最多只能买1000注
            	die(ajaxMsg("error","超过购买限制,最多购买1000注"));
            }
            $aData['iTotalNum'] = intval($_POST['lottery_totalnum']);
            if( !isset($_POST['lottery_istrace']) || !is_numeric($_POST['lottery_istrace']) )
            {//是否追号
                die(ajaxMsg("error","操作错误"));
            }
            $aData['bIsTrace'] = intval($_POST['lottery_istrace']) > 0 ? TRUE : FALSE;
            if( $aData['bIsTrace'] == FALSE )
            {//非追号形式获取信息
            	if( empty($_POST['lottery_totalamount']) || !is_numeric($_POST['lottery_totalamount']) 
                    || $_POST['lottery_totalamount'] <= 0 )
	            {//购买总金额
	                die(ajaxMsg("error","操作错误"));
	            }
	            $aData['iTotalAmount'] = intval($_POST['lottery_totalamount']);
	            if( empty($_POST['lottery_times']) || !is_numeric($_POST['lottery_times']) 
                    || $_POST['lottery_times'] <= 0 )
                {//购买倍数
                    die(ajaxMsg("error","请填写购买倍数"));
                }
                $aData['iTimes'] = intval($_POST['lottery_times']);
                //查看是否计算错误
                if( $aData['iTotalAmount'] != ($aData['iTotalNum'] * $aData['iTimes'] * 2)  )
                {
                	die(ajaxMsg("error","操作错误"));
                }
            }
            else 
            {//追号获取追号信息
            	if( empty($_POST['trace_totalamount']) || !is_numeric($_POST['trace_totalamount']) 
                    || $_POST['trace_totalamount'] <= 0 )
                {//追号总金额
                    die(ajaxMsg("error","操作错误"));
                }
                $aData['iTotalAmount'] = intval($_POST['trace_totalamount']);
                $aData['bIsTraceStop'] = (isset($_POST['trace_stop']) && intval($_POST['trace_stop'])>0) ? TRUE : FALSE;
                if( empty($_POST['trace_issue']) || !is_array($_POST['trace_issue']) )
	            {//追号期号
	                die(ajaxMsg("error","请选择追号期数"));
	            }
	            $aTraceIssue = $_POST['trace_issue'];
	            $aTempArr    = array();
	            $sTempArr    = "";//错误的期数
	            $iTempTimes  = 0;
	            foreach( $aTraceIssue as $v )
	            {
	            	if( empty($_POST['trace_times_'.$v]) || !is_numeric($_POST['trace_times_'.$v]) 
                    || $_POST['trace_times_'.$v] <= 0  )
                    {
                    	$sTempArr .= $v." ";
                    }
                    else 
                    {
	                    $aTempArr[$v] = intval($_POST['trace_times_'.$v]);
	                    $iTempTimes += intval($_POST['trace_times_'.$v]);
                    }
	            }
	            if( !empty($sTempArr) )
	            {
	            	die(ajaxMsg("error","追号中第[".$sTempArr."]期倍数错误"));
	            }
	            $aData['aTraceIssue'] = $aTempArr;
	            unset($aTempArr);
	            if( $aData['iTotalAmount'] != ($aData['iTotalNum'] * $iTempTimes * 2)  )
	            {
	            	die(ajaxMsg("error","操作错误"));
	            }
            }
            if( !empty($_POST['lottery_adjustcodes']) )
            {
            	if( empty($_POST['lottery_adjustchoice']) || !is_numeric($_POST['lottery_adjustchoice'])
            	    || ($_POST['lottery_adjustchoice'] != 1 && $_POST['lottery_adjustchoice'] != 2) )
           	    {//如果继续购买，则必须选择哪种购买方式[强制购买或者再次提示]
           	    	die(ajaxMsg("error","操作错误"));
           	    }
           	    $aData['iAdjustchoice']  = intval($_POST['lottery_adjustchoice']);
            	$aData['aAdjustedCodes'] = $_POST['lottery_adjustcodes'];
            }
            
            //02: 提交数据
            $oGamePlay = new model_gameplay();
            $mResult   = $oGamePlay->gameBuy( $iUserId, $iLotteryId, $iMethodId, $aData );
            if( $mResult === TRUE )
            {
            	die("success");
            }
            die($mResult);
		}
	}



	/**
	 * 靓号区
	 */
	function actionSpecial()
	{
		$iUserId = intval($_SESSION['userid']);
		//01: 公共数据检查
	    if( empty($_REQUEST['pid']) || !is_numeric($_REQUEST['pid']) || intval($_REQUEST['pid']) <= 0 )
        {//主菜单ID
            sysMsg( "操作错误", 2 );
        }
        $iTopMenuId     = intval($_REQUEST['pid']);
        $iCurrentMenuId = $iTopMenuId;//默认当前菜单为主菜单一样
        if( !empty($_REQUEST['curmid']) && is_numeric($_REQUEST['curmid']) && intval($_REQUEST['curmid']) > 0 )
        {//当前菜单ID
            $iCurrentMenuId = intval($_REQUEST['curmid']);
        }
	    //02：获取菜单信息
        $oUserMenu = new model_usermenu();
        $aTempArr  = $oUserMenu->userMenu( $iCurrentMenuId, "`lotteryid`,`methodid`" );
        if( empty($aTempArr) )
        {//没有具体玩法获取已关闭
        	if( isset($_POST['flag']) && $_POST['flag']== 'save' )
        	{
        		die(ajaxMsg("error","没有权限"));
        	}
            sysMsg( "没有权限", 2 );
        }
        if( empty($_POST['flag']) || $_POST['flag']!= 'save' )
        {
        	$aLocation[0] = array( "title"=>'系统公告',"url"=>url('default', 'start') );
            //01: 获取号码动态
            $oGame        = new model_gamespecial();
            $aCodeData    = $oGame->getDynamicCode( $iUserId, $aTempArr['lotteryid'], $aTempArr['methodid'] );
            if( $aCodeData === -1 )
            {
            	sysMsg( "没有权限", 2 );
            }
            elseif( $aCodeData === -2 || $aCodeData === -3 || $aCodeData === 0 )// || empty($aCodeData)
            {
            	sysMsg( "温馨提示：靓号尚未开售，请稍后再试。", 2, $aLocation );
            }
            //获取用户余额信息
            $oUserFund = new model_userfund();
            $aUserInfo = $oUserFund->getFundByUser($iUserId, "", SYS_CHANNELID);
            if( empty($aUserInfo) )
            {
                sysMsg( "你的资金被其他操作锁定", 2 );
            }
            
            //02: 获取子标签
            $sFields    = "`menuid`,`title`,`description`,`controller`,`actioner`";
            $sCondition = " AND `islabel`='1' AND `isdisabled`='0' AND `menutype`='0' ";
            $aLabel     = $oUserMenu->userMenuChild( $iTopMenuId, FALSE, $sFields, $sCondition );
            
            $aUserInfo['specialmaxtimes']   = intval(getConfigValue( 'specialmaxtimes', 1 ));
            $aUserInfo['bigordercancel']    = intval(getConfigValue( 'bigordercancel', 10000 ));
            $aUserInfo['bigordercancelpre'] = floatval(getConfigValue( 'bigordercancelpre', 0.01 ));
            $GLOBALS['oView']->assign( "pid", $iTopMenuId );            //主菜单ID
            $GLOBALS['oView']->assign( "curmid", $iCurrentMenuId );     //当前菜单ID
            $GLOBALS['oView']->assign( "labels", $aLabel );             //标签
            $GLOBALS['oView']->assign( "codedata", $aCodeData );        //号码奖金分组信息
            $GLOBALS['oView']->assign( "userinfo", $aUserInfo );        //用户信息
            $GLOBALS['oView']->assign( "method", $aTempArr );           //菜单信息
            $GLOBALS['oView']->assign( "sys_header_title", TRUE );      //不显示顶部[代理平台->]信息
            $oGame->assignSysInfo();
            $GLOBALS['oView']->display('game_special.html');
            exit;
        }
        elseif( $_POST['flag']== 'save' )
        {
        	//01:获取数据并检测
        	if( empty($_POST['lotteryid']) || intval($_POST['lotteryid']) != intval($aTempArr['lotteryid']) )
        	{//彩种
        		die(ajaxMsg("error","没有权限"));
        	}
        	$iLotteryId = intval($aTempArr['lotteryid']);
        	if( empty($_POST['methodid']) || intval($_POST['methodid']) != intval($aTempArr['methodid']) )
            {//玩法
                die(ajaxMsg("error","没有权限"));
            }
            $iMethodId = intval($aTempArr['methodid']);
            if( empty($_POST['issue']) )
            {
            	die(ajaxMsg("error","操作错误"));
            }
            $sIssue = $_POST['issue'];
            if( empty($_POST['select_codes']) || !is_array($_POST['select_codes']) )
            {//购买号码
                die(ajaxMsg("error","请选择投注号码"));
            }
            $aErrorCode = array();  //错误倍数号码
            $aCodes     = array();  //投注号码与倍数关系数组
            foreach( $_POST['select_codes'] as $v )
            {
            	if( !preg_match("/^([0-9]){3}$/", $v) )
            	{//检查号码是否正确
            		die(ajaxMsg("error","操作错误"));
            	}
            	if( empty($_POST['times_'.$v]) || !is_numeric($_POST['times_'.$v]) || $_POST['times_'.$v] <= 0 )
            	{//检测倍数是否正确
            		$aErrorCode[] = $v;
            	}
            	$aCodes[$v] = intval($_POST['times_'.$v]);
            	if( !empty($aErrorCode) )
            	{
            		die(ajaxMsg("error","投注号码中 [".implode(",",$aErrorCode)."] 的倍数错误 "));
            	}
            }
            if( empty($_POST['dynamicchoice']) || 
                (intval($_POST['dynamicchoice']) != 1 && intval($_POST['dynamicchoice']) != 2) )
            {
            	die(ajaxMsg("error","操作错误"));
            }
            $iChoice = intval($_POST['dynamicchoice']);
            //02: 进入投注程序
            $oGame   = new model_gamespecial();
            $mResult = $oGame->dynamicBuy( $iUserId, $iLotteryId, $iMethodId, $sIssue, $aCodes, $iChoice );
            if( $mResult !== TRUE )
            {
            	die($mResult);
            }
            die("success");  
        }
	}



	/**
	 * 奖金动态
	 *
	 */
    function actionCodeDynamic()
    {
        $iUserId = intval($_SESSION['userid']);
        if( empty($_REQUEST['pid']) || !is_numeric($_REQUEST['pid']) || intval($_REQUEST['pid']) <= 0 )
        {//主菜单ID
            sysMsg( "操作错误", 2 );
        }
        $iTopMenuId     = intval($_REQUEST['pid']);
        $iCurrentMenuId = $iTopMenuId;//默认当前菜单为主菜单一样
        if( !empty($_REQUEST['curmid']) && is_numeric($_REQUEST['curmid']) && intval($_REQUEST['curmid']) > 0 )
        {//当前菜单ID
            $iCurrentMenuId = intval($_REQUEST['curmid']);
        }
        
        //02：获取菜单信息
        $oUserMenu = new model_usermenu();
        $aTempArr  = $oUserMenu->userMenu( $iCurrentMenuId, "`lotteryid`,`methodid`" );
        if( empty($aTempArr) )
        {//没有具体玩法获取已关闭
            sysMsg( "没有权限", 2 );
        }
        
        
        //03: 获取号码动态
        $oGame       = new model_gamespecial();
        $aCodeData = $oGame->getDynamicCode( $iUserId, $aTempArr['lotteryid'], $aTempArr['methodid'], FALSE );
        if( $aCodeData === -1 )
        {
            sysMsg( "没有权限", 2 );
        }
        elseif( $aCodeData === -2 || $aCodeData === -3 || empty($aCodeData) )
        {
            sysMsg( "温馨提示：靓号尚未开售，请稍后再试。", 2 );
        }
        
        //04: 获取父标签的子标签
        $sFields   = "`menuid`,`title`,`description`,`controller`,`actioner`";
        $sCondition= " AND `islabel`='1' AND `isdisabled`='0' AND `menutype`='0' ";
        $aLabel = $oUserMenu->userMenuChild( $iTopMenuId, FALSE, $sFields, $sCondition );
        
        $GLOBALS['oView']->assign( "pid", $iTopMenuId );            //主菜单ID
        $GLOBALS['oView']->assign( "curmid", $iCurrentMenuId );     //当前菜单ID
        $GLOBALS['oView']->assign( "labels", $aLabel );             //标签
        $GLOBALS['oView']->assign( "codedata", $aCodeData );        //号码奖金分组信息
        $GLOBALS['oView']->assign( "sys_header_title", TRUE );
        $oGame->assignSysInfo();
        $GLOBALS['oView']->display('game_codedynamic.html');
        exit;
    }



    /**
     * 查看历史中奖号码
     * 
     * @author mark
     */
    function actionBonusCode()
    {
    	$iLotteryId	 = isset($_GET['lotteryid']) && is_numeric($_GET['lotteryid']) ? intval($_GET['lotteryid']) : 1;
    	$iIssueCount = isset($_GET['issuecount']) && is_numeric($_GET['issuecount']) ? intval($_GET['issuecount']) : 30;
    	if( !in_array($iIssueCount, array(30, 50, 100)) )
    	{
    		$iIssueCount = 30;
    	}
    	$sStartIssue = isset($_POST['startissue']) ? $_POST['startissue'] : '';
    	$sEndIssue	 = isset($_POST['endissue']) ? $_POST['endissue'] : '';
    	$sWhere      = 1;
    	$oLottery    = new model_lottery();
    	//获取游戏基本信息
    	$aLottery    = $oLottery->lotteryGetOne( 'cnname,numberrule,issuerule', "lotteryid=" . $iLotteryId );
    	if( $sStartIssue )
    	{
    		if( !$this->checkIssue($sStartIssue, $aLottery['issuerule']) )
    		{
    			sysMsg( '输入的开始奖期不符合规则！', 2 );
    		}
    		$sWhere .= " AND issue >= '$sStartIssue' ";//查询开始期数
    	}
    	if( $sEndIssue )
    	{
    		if( !$this->checkIssue($sEndIssue, $aLottery['issuerule']) )
    		{
    			sysMsg( '输入的结束奖期不符合规则！', 2 );
    		}
    		$sWhere .= " AND issue <= '$sEndIssue' ";//查询结束期数
    	}
    	$aValidNUmString   = unserialize( $aLottery['numberrule'] );
    	$oGameManage       = new model_gamemanage();
    	$aValidNUm[]       = range( $aValidNUmString['startno'], $aValidNUmString['endno'] );//获取游戏有效号码
    	$aHistoryBonusCode = $oGameManage->getHistoryBounsCode( $iLotteryId, $iIssueCount, $sWhere );//获取历史中奖号码
    	krsort($aHistoryBonusCode);
    	$temp_HistoryBonusCode = array();
    	foreach ( $aHistoryBonusCode as $key => $aBonusCode )
    	{
    		$aWeiCode                           = str_split($aBonusCode['code'],1);//拆分中奖号码每一位
    		$temp_HistoryBonusCode[$key] 		= $aBonusCode;
    		$temp_HistoryBonusCode[$key]['wei']	= $aWeiCode;
    	}
    	$aCodeGroup = array();
    	for( $i = 0; $i < $aValidNUmString['len']; $i++ )//号码组
    	{
    		$aCodeGroup[] = array(	'wei' 			=> $i,
    								'ballstytle' 	=> $i%2+1,
    								'normalstytle'	=> $i%2+3
    							);
    	}
    	$GLOBALS['oView']->assign( 'vaildnum', $aValidNUm[0] );
    	$GLOBALS['oView']->assign( "ur_here", "查看历史号码走势" );
    	$GLOBALS['oView']->assign( "lotteryid", $iLotteryId );
    	$GLOBALS['oView']->assign( "bonuscode", $temp_HistoryBonusCode );
    	$GLOBALS['oView']->assign( "bonuscodelength", $aValidNUmString['len'] );
    	$GLOBALS['oView']->assign( "lotteryname", $aLottery['cnname'] );
    	$GLOBALS['oView']->assign( "acodegroup", $aCodeGroup );
    	$GLOBALS['oView']->assign( "startissue", $sStartIssue );
    	$GLOBALS['oView']->assign( "endissue", $sEndIssue );
    	unset($temp_HistoryBonusCode);
		$oGameManage->assignSysInfo();
		$GLOBALS['oView']->display( "game_bonuscode$iLotteryId.html" );
		exit;
    }



    /**
	 * 检测输入的奖期号是否符合规则
	 *
	 * @param string $sIssue		检测的奖期号码
	 * @param string $sIssueRule	奖期规则
	 * 
	 * @author mark
	*/
    function checkIssue( $sIssue, $sIssueRule )
    {
    	//生成奖期规则正则表达式
    	$sPattern = str_replace( '-', '[-]', $sIssueRule );
    	$sPattern = str_replace( '(Y)', '(20)\d{2}', $sPattern );
    	$sPattern = str_replace( '(y)', '\d{2}', $sPattern );
    	$sPattern = str_replace( '(M)', '(0[1-9]|1[0-2])', $sPattern );
    	$sPattern = str_replace( '(D)', '(0[1-9]|[1-2]\d|[3][0-1])', $sPattern );
    	preg_match_all( "/\([N,T](.*)\)/", $sPattern, $aIssueOrder );
    	$iIssueOrderLength = $aIssueOrder[1][0];
    	$sPattern = preg_replace( "/\([N,T](.*)\)/", '\d{' . $iIssueOrderLength . '}', $sPattern );//规则正则表达式
    	$sPattern = "/^" . $sPattern . "$/";
    	return preg_match( $sPattern, $sIssue );
    }
}
?>