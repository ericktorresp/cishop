<?php
/**
 * 文件 : /_app/controller/gameinfo.php
 * 功能 : 控制器 - 用户游戏信息的查看
 *  
 * + actionGemelist     用户投单记录
 * + actionGameDetail   注单详情
 * + actionCancelGame   用户撤单
 * + actionTask         用户追号记录
 * + actionTaskDetail   追号详情
 * + actionCancelTask   取消追号
 * 
 * @author    james,saul    090914
 * @version   1.2.0
 * @package   lowgame  
 */

class controller_gameinfo extends basecontroller 
{
    /**
     * 参与游戏信息
     * URL: ./index.php?controller=gameinfo&action=gamelist
     * @author SAUL
     */
    function actionGamelist()
    {//查询自身的
        //固定参数的传递
        $oMethod  = new model_method();
        //玩法
        $aMethod  = $oMethod->methodGetList( "a.`methodid`,a.`methodname`,a.`lotteryid`,b.`cnname`",
                                             "a.`pid`>0", "", "", 0 );
        $aLottery = array();
        $aMethods = array();
        foreach( $aMethod as $method )
        {
            $aLottery[$method["lotteryid"]]                         = $method["cnname"];
            $aMethods[$method["lotteryid"]][$method["methodid"]]    = $method;
        }
        $sWhere = " ";
        //开始时间
        if( isset($_GET["starttime"]) && !empty($_GET["starttime"]) )
        {
            $sStartTime = getFilterDate( $_GET["starttime"] );
        }
        else
        {
			// 6/9/2010
        	if ( date("Y-m-d H:i:s", strtotime("NOW") ) < date("Y-m-d 02:20:00") )
        	{
        		$sStartTime = getFilterDate( date("Y-m-d 02:20:00", strtotime("-1 days")  ) );
        	}
        	else
        	{
            	$sStartTime = getFilterDate( date("Y-m-d 02:20:00") );
        	}
        }
        if( $sStartTime != "" )
        {
            $sWhere            .= " AND P.`writetime`>='".$sStartTime."'";
            $aHtml["starttime"] = $sStartTime;
        }
        //结束时间
        if( isset($_GET["endtime"]) && !empty($_GET["endtime"]) )
        {
            $sEndTime = getFilterDate( $_GET["endtime"] );
        }
        else
        {
        	// 6/9/2010
        	if ( date("Y-m-d H:i:s", strtotime("NOW") )  < date("Y-m-d 02:20:00") )
        	{
        		$sEndTime = getFilterDate( date("Y-m-d 02:20:00") );
        	}
        	else
        	{
            	$sEndTime = getFilterDate( date("Y-m-d 02:20:00", strtotime("+1 days") ) );
        	}
        }
        if( $sEndTime != "" )
        {
            $aHtml["endtime"] = $sEndTime;
            $sWhere          .= " AND P.`writetime`<='".$sEndTime."'";
        }
        $oIssue = new model_issueinfo();
        $aIssue = array();
        foreach( $aLottery as $iLotteryid=>$aTemp )
        {
            $aIssue[$iLotteryid] = $oIssue->issueGetList(" A.`issue`,DATE(A.`saleend`) AS dateend ",
                                                         " B.`lotteryid`='".$iLotteryid."' "
                                                        ." AND UNIX_TIMESTAMP(A.`salestart`)<UNIX_TIMESTAMP(now()) ",
                                                         " A.`saleend` DESC LIMIT 0,10", 0 );
        }
        $GLOBALS['oView']->assign( "lottery", $aLottery );
        $GLOBALS['oView']->assign( "data_method", json_encode($aMethods) );
        $GLOBALS["oView"]->assign( "data_issue", json_encode($aIssue) );
        //彩种
        $iLotteryId = isset($_GET["lotteryid"]) && is_numeric($_GET["lotteryid"]) ? intval($_GET["lotteryid"]) : 0;
        $aHtml["lotteryid"] = $iLotteryId;
        if( $iLotteryId >0 )
        { 
            $sWhere   .=" AND P.`lotteryid`='".$iLotteryId."' ";
            //玩法
            $iMethodId = isset($_GET["methodid"]) && is_numeric($_GET["methodid"]) ? intval($_GET["methodid"]) : 0;
            if( $iMethodId>0 )
            {
                $sWhere .= " AND P.`methodid`='".$iMethodId."' ";
            }
            //奖期
            $sIssue = isset($_GET["issue"]) && !empty($_GET["issue"]) ? daddslashes($_GET["issue"]) : "0" ;
            if( $sIssue <>"0" )
            {
                $sWhere .= " AND P.`issue`='".$sIssue."'";
            }
            $aHtml["methodid"]  = $iMethodId;
            $aHtml["issue"]     = $sIssue;
        }
        else
        {
            $aHtml["methodid"]  = 0;
            $aHtml["issue"]     = 0;
        }
        //下面是Code
        if( isset($_GET["projectno"]) && !empty($_GET["projectno"]) )
        {
            $iProjectNo = model_projects::ProjectEnCode( $_GET["projectno"], "DECODE" );
            if( intval($iProjectNo)>0 )
            {
                $aHtml["projectno"] = daddslashes($_GET["projectno"]);
                $sWhere             = " AND P.`projectid`='".$iProjectNo."' ";
            }
        }
        $sWhere      .= " AND P.`userid`='".$_SESSION["userid"]."' "; //只能查看自身部分
        $oProjects    = new model_projects( $GLOBALS['aSysDbServer']['report'] );
        $iPage        = isset($_GET["p"]) && is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
        $aProjects    = $oProjects->projectGetResult( 0, TRUE, "", $sWhere, "P.`projectid` DESC", 25, $iPage );
        $total["in"]  = 0.00;
        $total["out"] = 0.00;
        foreach( $aProjects["results"] as $i=>&$aProject )
        {
            $aProject["projectid"] = model_projects::ProjectEnCode("D".$aProject["issue"]."-".$aProject["projectid"],
                                                                   "ENCODE");
            $total["in"]           = $total["in"]  + $aProject["bonus"];
            $total["out"]          = $total["out"] + $aProject["totalprice"];
            //对号码进行整理
            if( strlen( $aProject["code"] ) > 20 )
            {
                $str  = "<a href=\"javascript:show_no('".$i."');\">详细号码</a>";
                $str .= "<div class=\"task_div\" id=\"code_".$i."\">号码详情";
                $str .= "[<a href=\"javascript:close_no('".$i."');\">关闭</a>]<br/>";
                $str .= "<textarea class=\"code\" readonly=\"readonly\">";
                $code = str_replace( array("B","S","A","D","|"),array("大","小","单","双",","), $aProject["code"]);
                $sTempCode = "";
                $sProjectCode = "";
                $aCodeDetail = explode(",",$code);
                foreach ( $aCodeDetail as $sCode )
                {
                    $sTempCode .= $sCode .",";
                    $sProjectCode .= $sCode .","; 
                    if( strlen($sTempCode) >= 44 )
                    {
                         $sProjectCode = substr($sProjectCode, 0,-1);
                         $sProjectCode .= "\r\n";
                         $sTempCode = "";
                    }
                }
                $sProjectCode = substr($sProjectCode, 0,-1);
                $str .= $sProjectCode."</textarea></div>";
                $aProject["code"] =$str;
            }
            else
            {
                $aProject["code"] = str_replace( array("B","S","A","D","|"),array("大","小","单","双",","), 
                                                 $aProject["code"]);
            }
        }
        $oPage        = new pages( $aProjects["affects"], 25 );
        $aLocation[0] = array( 'text'=>'清空查询条件', "href"=>url('gameinfo','gamelist') );
        $GLOBALS['oView']->assign( "total",         $total );
        $GLOBALS['oView']->assign( "aProject",      $aProjects["results"] );
        $GLOBALS['oView']->assign( "pageinfo",      $oPage->show(1) );
        $GLOBALS['oView']->assign( "s",             $aHtml );
        $GLOBALS['oView']->assign( "actionlink",    $aLocation[0] );
        $GLOBALS['oView']->assign( "ur_here",       "参与游戏信息" );
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display( "gameinfo_gamelist.html" );
        EXIT;
    }



    /**
     * 查看注单详情
     * URL: index.php?controller=gameinfo&action=gamedetail
     * @author:SAUL
     */
    function actionGamedetail()
    {
        $aLocation[0]   = array( "title"=>'参与游戏信息', "url"=>url('gameinfo','gamelist') );
        $iProjectId     = !empty($_GET["id"]) ? model_projects::ProjectEnCode($_GET["id"], "DECODE") : 0;
        if( $iProjectId == 0 )
        {
            sysMsg( '权限不足', 2, $aLocation );
        }
        $oProject   = new model_projects();
        $sFields    = " P.*,L.`cnname`,M.`methodname`,UT.`username`,I.`code` AS `nocode`,I.`canneldeadline` ";
        $sCondtion  = " AND `projectid`='".$iProjectId."' ";
        $aProject   = $oProject->projectGetResult( intval($_SESSION["userid"]), TRUE, $sFields, $sCondtion, '', 0);
        if( empty($aProject[0]) )
        {
            sysMsg( '单子不存在', 2, $aLocation );
        }
        $iSign = 0;
        //注单编号
        if(intval($aProject[0]["taskid"])>0)
        {
            $oTask = new model_task();
            $aTask = $oTask->taskgetList(0,TRUE,"T.`taskid`,T.`beginissue`"," and T.`taskid`='".$aProject[0]["taskid"]."'","",0);
            // 获取追号单开始时间
            $aResult = $oTask->getTaskInfo($aProject[0]['taskid']);
            $aProject[0]["taskid"] = model_task::TaskEnCode("T".$aTask[0]["beginissue"]."-".$aTask[0]["taskid"],"ENCODE"); 
            if ($aResult['begintime'] >= getConfigValue( 'bigorderstarttime', '00-00-00 00:00:00' )){
            	$iSign = 1;
            }
        } else {
        	$iSign = 1;
        }
        $GLOBALS['oView']->assign( "ur_here", "查看注单详情" );
        $iBigmoney = intval( getConfigValue( 'bigordercancel', 10000 ) );
        if( $iSign === 1 && $aProject[0]["totalprice"] >= $iBigmoney )
        {
            $fBig   = getConfigValue('bigordercancelpre', 0.01 ); //大额撤单手续费用
            $fMoney = number_format($fBig * $aProject[0]["totalprice"], 2, '.', '');
            $GLOBALS["oView"]->assign( "need", 1 );
            $GLOBALS['oView']->assign( "money", $fMoney );
        }
        if( strtotime($aProject[0]["canneldeadline"]) > time() && $aProject[0]['iscancel'] == 0 
            && $aProject[0]["userid"] == intval($_SESSION["userid"]) )
        { //前台是否能够撤单
            $GLOBALS['oView']->assign( "can", 1 );
        }
        else
        {
            if( $aProject[0]["isgetprize"] == 2 )
            {//奖金详情
                $sDescription = $oProject->getProjectBonusDescription( $aProject[0] );
                $GLOBALS['oView']->assign( "description", $sDescription );
            }
        }
        //获取扩展号码
        $sCondition          = " `projectid`='".$aProject[0]["projectid"]."' ";
        $aPrizelevel         = $oProject->getExtendCode( "*", $sCondition, "`isspecial` ASC", 0 );
        $aProject[0]["code"] = wordwrap( str_replace( array("B","S","A","D","|"), array("大","小","单","双",", "), 
                                         $aProject[0]["code"]),100,"<br/>");
        $aProject[0]["projectid"] = model_projects::ProjectEnCode(
                                                "D".$aProject[0]["issue"]."-".$aProject[0]["projectid"], "ENCODE" );
        $GLOBALS['oView']->assign( "project", $aProject[0] );
        foreach($aPrizelevel as &$prizelevel)
        {
        	$prizelevel["singleprize"] = $prizelevel["prize"] / $prizelevel["codetimes"];
            $prizelevel["expandcode"] = wordwrap( str_replace(array("|","#"), array(", ","|"), 
                                                  $prizelevel["expandcode"]), 100, "<br>" );
        }
        $GLOBALS['oView']->assign("prizelevel",$aPrizelevel);
        $oProject->assignSysInfo();
        $GLOBALS['oView']->display( "gameinfo_gamedetail.html" );
        EXIT;
    }



    /**
     * 用户撤单
     * URL:./index.php?controller=gameinfo&action=cancelgame
     * @author JAMES
     */ 
    function actionCancelGame()
    {
        $sProjectNo   = !empty($_GET["id"]) ? $_GET["id"] : "";
        $aLocation[0] = array( "title"=>'查看注单详情', "url"=>url('gameinfo','gamedetail',array('id'=>$sProjectNo)) );
        $iProjectId   = !empty($sProjectNo) ? model_projects::ProjectEnCode($sProjectNo, "DECODE") : 0;
        if( $iProjectId == 0 )
        {
            sysMsg( '权限不足', 2, $aLocation );
        }
        $oGame   = new model_gamemanage();
        $mResult = $oGame->cancelProject( intval($_SESSION["userid"]), $iProjectId );
        if( $mResult === TRUE )
        {
            sysMsg( '撤单成功', 1, $aLocation );
        }
        else
        {
            sysMsg( $mResult, 2, $aLocation );
        }
    }



    /**
     * 追号记录
     * @author SAUL
     * URL:index.php?controller=gameinfo&action=task
     */
    function actionTask()
    {//查询自身的
        //固定参数的传递
        $oMethod = new model_method();
        //玩法
        $aMethod = $oMethod->methodGetList( "a.`methodid`,a.`methodname`,a.`lotteryid`,b.`cnname`",
                                            "a.`pid`>0", "", "", 0 );
        $aLottery = array();
        $aMethods = array();
        foreach( $aMethod as $method )
        {
            $aLottery[$method["lotteryid"]]                      = $method["cnname"];
            $aMethods[$method["lotteryid"]][$method["methodid"]] = $method;
        }
        $GLOBALS['oView']->assign( "lottery",     $aLottery );
        $GLOBALS['oView']->assign( "data_method", json_encode($aMethods) );
        //参数整理
        $sWhere = "";
        //开始时间
        if( isset($_GET["starttime"]) && !empty($_GET["starttime"]) )
        {
            $sStartTime = getFilterDate( $_GET["starttime"] );
        }
        else
        {
        	// 6/9/2010
        	if ( date("Y-m-d H:i:s", strtotime("NOW") ) < date("Y-m-d 02:20:00") )
        	{
        		$sStartTime = getFilterDate( date("Y-m-d 02:20:00", strtotime("-1 days")  ) );
        	}
        	else
        	{
            	$sStartTime = getFilterDate( date("Y-m-d 02:20:00") );
        	}
        }
        if( $sStartTime != "" )
        {
            $sWhere            .= " AND T.`begintime`>'".$sStartTime."'";
            $aHtml["starttime"] = $sStartTime;
        }
        //结束时间
        if( isset($_GET["endtime"]) && !empty($_GET["endtime"]) )
        {
            $sEndtime = getFilterDate($_GET["endtime"]);
        }
        else
        {
        	// 6/9/2010
        	if ( date("Y-m-d H:i:s", strtotime("NOW") ) < date("Y-m-d 02:20:00") )
        	{
        		$sEndtime = getFilterDate( date("Y-m-d 02:20:00"  ) );
        	}
        	else
        	{
            	$sEndtime = getFilterDate( date("Y-m-d 02:20:00",strtotime("+1 days")) );
        	}
        }
        if( $sEndtime != "" )
        {
        	$aHtml["endtime"] = $sEndtime;
            $sWhere          .= " AND T.`begintime`<'".$sEndtime."'";
        }
        $oIssue = new model_issueinfo();
        $aIssue = array();
        foreach( $aLottery as $iLottery=>$temp )
        {
            $aIssue[$iLottery] = $oIssue->issueGetList( " A.`issue`,date(A.`saleend`) AS dateend ",
                                                        " B.`lotteryid`='".$iLottery."'"
                                                       ." AND UNIX_TIMESTAMP(A.`salestart`)<UNIX_TIMESTAMP(now()) ",
                                                        " A.`saleend` DESC limit 0,10", 0 );
        }
        $GLOBALS["oView"]->assign( "data_issue", json_encode($aIssue) );
        $iLotteryId = isset($_GET["lotteryid"]) && is_numeric($_GET["lotteryid"]) ? intval($_GET["lotteryid"]) : 0;
        $aHtml["lotteryid"] = $iLotteryId;
        if( $iLotteryId >0 )
        {
            $sWhere .=" AND T.`lotteryid`='".$iLotteryId."' ";
            //玩法
            $iMethodId = isset($_GET["methodid"]) && is_numeric($_GET["methodid"]) ? intval($_GET["methodid"]) : 0;
            if( $iMethodId > 0 )
            {
                $sWhere .= " AND T.`methodid`='".$iMethodId."' ";
            }
            //彩种ID
            $sIssue = isset($_GET["issue"]) && !empty($_GET["issue"]) ? daddslashes($_GET["issue"]) : "0";
            if( $sIssue != "0" )
            {
                $sWhere .= " AND T.`beginissue`='".$sIssue."'";
            }
            $aHtml["methodid"]  = $iMethodId;
            $aHtml["issue"]     = $sIssue;
        }
        else
        {
            $aHtml["methodid"]  = 0;
            $aHtml["issue"]     = 0;
        }
        //下面是Code
        if( isset($_GET["taskno"]) && !empty($_GET["taskno"]) )
        {
            $iTaskId = model_task::TaskEnCode( $_GET["taskno"], "DECODE" );
            if( $iTaskId>0 )
            {
                $aHtml["taskno"] = daddslashes( $_GET["taskno"] );
            }
            $sWhere = " AND T.`taskid`='".$iTaskId."' ";
        }
        $sWhere         .= " AND T.`userid`='".intval($_SESSION["userid"])."' ";
        $iPage           = isset($_GET["p"]) && is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
        $oTask           = new model_task( $GLOBALS['aSysDbServer']['report'] );
        $aTask           = $oTask->taskgetList( 0, TRUE, '', $sWhere, "T.`taskid` DESC", 25, $iPage );
        $total["total"]  = 0.00;
        $total["finish"] = 0.00;
        $total["cancel"] = 0.00;
        foreach( $aTask["results"] as $i=>&$task )
        {
            $task["taskid"]  = model_task::TaskEnCode( "T".$task["beginissue"]."-".$task["taskid"], "ENCODE" );
            $total["total"]  = $total["total"]  + $task["taskprice"];
            $total["finish"] = $total["finish"] + $task["finishprice"];
            $total["cancel"] = $total["cancel"] + $task["cancelprice"];
            //对号码进行整理
            if( strlen($task["codes"]) > 20 )
            {
                $str  = "<a href=\"javascript:show_no('".$i."');\">详细号码</a>";
                $str .= "<div class=\"task_div\" id=\"code_".$i."\">号码详情";
                $str .= "[<a href=\"javascript:close_no('".$i."');\">关闭</a>]<br/>";
                $str .= "<textarea class=\"code\" readonly=\"readonly\">";
                $code = str_replace( array("B","S","A","D","|"),array("大","小","单","双",","), $task["codes"]);
                $str .= $code."</textarea></div>";
                $task["codes"] =$str;
            }
            else
            {
                $task["codes"] =str_replace( array("B","S","A","D","|"), array("大","小","单","双",","), $task["codes"]);
            }
        }
        $oPage     = new pages( $aTask["affects"], 25 );
        $aLocation = array( "text"=>'清空查询条件', "href"=>url('gameinfo','task') );
        $GLOBALS['oView']->assign( "total", $total );
        $GLOBALS['oView']->assign( "aTask", $aTask["results"] );
        $GLOBALS['oView']->assign( "pageinfo", $oPage->show(1) );
        $GLOBALS['oView']->assign( "s",     $aHtml );
        $GLOBALS['oView']->assign( "actionlink", $aLocation );
        $GLOBALS["oView"]->assign( "ur_here", "查看追号信息" );
        $oTask->assignSysInfo();
        $GLOBALS['oView']->display( "gameinfo_task.html" );
        EXIT;
    }



    /**
     * 查看追号详情
     * URL:./index.php?controller=gameinfo&action=taskdetail
     * @author:SAUL
     */
    function actionTaskdetail()
    {
        $aLocation[0]   = array("title"=>'查看追号记录',"url"=>url('gameinfo','task'));
        $iTaskId        = isset($_GET["id"])&&!empty($_GET["id"]) ? model_task::TaskEnCode($_GET["id"], "DECODE") : 0;
        if( $iTaskId==0 )
        {
            sysMsg('没有权限', 2, $aLocation );
        }
        $oTask = new model_task();
        $aTask = $oTask->taskgetList(intval($_SESSION["userid"]), FALSE, '', " AND T.`taskid`='".$iTaskId."'", '', 0);	
        if( empty($aTask[0]) )
        {
            sysMsg('追号单不存在', 2, $aLocation );
        }
        $aTask[0]["codes"]  = wordwrap( str_replace( array("B","S","A","D","|"), array("大","小","单","双",", "), 
                                        $aTask[0]["codes"]), 100, "<br/>" );
        $aTask[0]["taskid"] = model_task::TaskEnCode( "T".$aTask[0]["beginissue"]."-".$aTask[0]["taskid"], "ENCODE" );
        $aTaskDetail        = $oTask->taskdetailGetList( $iTaskId, $aTask[0]["lotteryid"] ); 
        // 获取追号单的每期单倍价格
        $aResult = $oTask->getTaskInfo($aTaskDetail[0]['taskid']);
        // 收取手续费的最高限制
        $fBigMoney = intval( getConfigValue( 'bigordercancel', 10000 ) );
        $fBigFee   = getConfigValue('bigordercancelpre', 0.01 ); //大额撤单手续费用
        foreach( $aTaskDetail as &$aDetail )
        {
        	if( $aDetail["projectid"]>0 )
        	{ //注单详情
        		$aDetail["projectid"] = model_projects::ProjectEnCode( "D".$aDetail["issue"]."-".$aDetail["projectid"] , "ENCODE" );
        	}
        	// 计算每一期的投注金额
        	$fMoney = $aDetail['multiple'] * $aResult['singleprice'];
        	// 计算每一期如果终止追号，是否要收取手续费，如果要收取，应当收取多少
        	$aDetail['fee'] = ($fMoney >= $fBigMoney) ? number_format($fMoney * $fBigFee, 2, '.', '') : 0;
        }
        $need = 0; // 是否收手续费标志位
        // 根据开始收取撤大额追号单手续费的时间判断当前单是否要收手续费
        if ($aTask[0]['begintime'] >= getConfigValue( 'bigorderstarttime', '00-00-00 00:00:00' )){
        	$need = 1;
        }
        $GLOBALS["oView"]->assign( "task", $aTask[0] );
        $GLOBALS['oView']->assign( "aTaskdetail", $aTaskDetail );
        $GLOBALS['oView']->assign( "need", $need );
        $GLOBALS['oView']->assign( "ur_here", "查看追号详情" );
        $oTask->assignSysInfo();
        $GLOBALS['oView']->display("gameinfo_taskdetail.html");
        EXIT;
    }



    /**
     * 追号单撤单
     * URL: ./index.php?controller=gameinfo&action=canceltask
     * @author:JAMES
     */
    function actionCanceltask()
    {
    	$sTaskNo      = !empty($_POST["id"]) ? $_POST["id"] : "";
        $aLocation[0] = array("title"=>'查看追号详情',"url"=>url('gameinfo', 'taskdetail', array('id'=>$sTaskNo)));
        $iTaskId      = !empty($sTaskNo) ? model_projects::ProjectEnCode($sTaskNo, "DECODE") : 0;
        if( $iTaskId == 0 )
        {
            sysMsg( '权限不足', 2, $aLocation );
        }
        $aId = !empty($_POST["taskid"]) ?$_POST["taskid"]: array();
        $oGame   = new model_gamemanage();
        $mResult = $oGame->cancelTask( intval($_SESSION["userid"]), $iTaskId, $aId, '', true );
        if( $mResult === TRUE )
        {
            sysMsg( '操作成功', 1, $aLocation );
        }
        else
        {
            sysMsg( $mResult, 2, $aLocation );
        }
    }
}
?>